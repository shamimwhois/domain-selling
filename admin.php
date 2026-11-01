<?php
/**
 * Admin panel — create & update the site content stored in app/data.php.
 * Every save writes a timestamped backup to app/backups/ first, so changes
 * can be rolled back from the "Restore backup" box.
 */
declare(strict_types=1);

session_start();

define('DATA_FILE', __DIR__ . '/app/data.php');
define('BACKUP_DIR', __DIR__ . '/app/backups');

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['admin_csrf'];

$data    = is_file(DATA_FILE) ? require DATA_FILE : [];
$saved   = false;
$errors  = [];
$restored = '';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function fsan($v): string
{
    return trim((string) ($v ?? ''));
}

function f_allowed(string $v, array $allowed, string $default): string
{
    return in_array($v, $allowed, true) ? $v : $default;
}

function export_array(array $data, int $level = 0): string
{
    $pad  = str_repeat('    ', $level + 1);
    $out  = "[\n";
    $isList = array_is_list($data);
    foreach ($data as $k => $v) {
        $key = $isList ? '' : var_export((string) $k, true) . ' => ';
        if (is_array($v)) {
            $out .= $pad . $key . export_array($v, $level + 1) . ",\n";
        } else {
            $out .= $pad . $key . var_export($v, true) . ",\n";
        }
    }
    return $out . str_repeat('    ', $level) . ']';
}

function write_data_file(array $data): void
{
    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }
    if (is_file(DATA_FILE)) {
        copy(DATA_FILE, BACKUP_DIR . '/data.' . date('Ymd_His') . '.php');
    }
    $content = "<?php\n"
        . "// All page data for index.php — edit content here, template renders it in loops.\n"
        . "// Managed by admin.php (last saved " . date('Y-m-d H:i:s') . ").\n\n"
        . 'return ' . export_array($data) . ";\n";
    file_put_contents(DATA_FILE, $content, LOCK_EX);
}

function list_backups(): array
{
    if (!is_dir(BACKUP_DIR)) {
        return [];
    }
    $files = glob(BACKUP_DIR . '/data.*.php') ?: [];
    rsort($files);
    return $files;
}

// ---------------------------------------------------------------------------
// POST handling
// ---------------------------------------------------------------------------

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = fsan($_POST['action'] ?? '');

    if (!hash_equals($csrf, fsan($_POST['csrf'] ?? ''))) {
        $errors[] = 'Session expired, please try again.';
    } elseif ($action === 'restore' && in_array(fsan($_POST['backup'] ?? ''), list_backups(), true)) {
        $backup = fsan($_POST['backup'] ?? '');
        copy($backup, DATA_FILE);
        $restored = basename($backup);
        $data = require DATA_FILE;
    } elseif ($action === 'save') {
        // --- fixed sections -------------------------------------------------
        $title = fsan($_POST['meta']['title'] ?? '');
        if ($title === '') {
            $errors[] = 'Site title is required.';
        }
        $meta = [
            'title'    => $title,
            'language' => preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', fsan($_POST['meta']['language'] ?? ''))
                ? fsan($_POST['meta']['language']) : 'en',
        ];

        $brand = [
            'logo_html'    => fsan($_POST['brand']['logo_html'] ?? ''),
            'name'         => fsan($_POST['brand']['name'] ?? ''),
            'extension'    => fsan($_POST['brand']['extension'] ?? ''),
            'badge'        => fsan($_POST['brand']['badge'] ?? ''),
            'hero_heading' => fsan($_POST['brand']['hero_heading'] ?? ''),
        ];

        $phone_raw = fsan($_POST['contact']['phone_raw'] ?? '');
        $wa_num    = preg_replace('/\D+/', '', fsan($_POST['contact']['whatsapp_number'] ?? ''));
        if ($phone_raw !== '' && !preg_match('/^\+?[0-9 ()-]{6,20}$/', $phone_raw)) {
            $errors[] = 'Phone (raw) may only contain digits, +, spaces, dashes.';
        }
        $contact = [
            'phone_display'   => fsan($_POST['contact']['phone_display'] ?? ''),
            'phone_raw'       => $phone_raw,
            'whatsapp_number' => $wa_num,
            'whatsapp_text'   => fsan($_POST['contact']['whatsapp_text'] ?? ''),
        ];

        // --- repeaters ------------------------------------------------------
        $repeater = function (string $section, array $keys, callable $build) {
            $count = max(0, min(30, (int) ($_POST[$section . '_count'] ?? 0)));
            $rows  = [];
            for ($i = 0; $i < $count; $i++) {
                $in = [];
                foreach ($keys as $k) {
                    $in[$k] = fsan($_POST[$section][$i][$k] ?? '');
                }
                $row = $build($in);
                if ($row !== null) {
                    $rows[] = $row;
                }
            }
            return $rows;
        };

        $nav_links = $repeater('nav_links', ['label', 'href'], fn($r) => ($r['label'] !== '' || $r['href'] !== '')
            ? ['label' => $r['label'], 'href' => $r['href']] : null);

        $hero_actions = $repeater('hero_actions', ['type', 'label'], fn($r) => ($r['label'] !== '')
            ? ['type' => f_allowed($r['type'], ['whatsapp', 'call'], 'call'), 'label' => $r['label']] : null);
        if (count($hero_actions) < 1) {
            $errors[] = 'At least one hero button is required (the floating contact buttons reuse it).';
        }

        $contact_info = $repeater('contact_info', ['label', 'value'], fn($r) => ($r['label'] !== '' || $r['value'] !== '')
            ? ['label' => $r['label'], 'value' => $r['value']] : null);

        $benefits = $repeater('benefits', ['color', 'title', 'text', 'icon_d'], fn($r) => ($r['title'] !== '' || $r['text'] !== '')
            ? [
                'color'  => f_allowed($r['color'], ['indigo', 'purple', 'emerald', 'amber', 'rose', 'sky', 'teal', 'cyan', 'violet', 'fuchsia'], 'indigo'),
                'title'  => $r['title'],
                'text'   => $r['text'],
                'icon_d' => $r['icon_d'],
            ] : null);

        // --- form section ---------------------------------------------------
        $form = [
            'title'       => fsan($_POST['form']['title'] ?? ''),
            'subtitle'    => fsan($_POST['form']['subtitle'] ?? ''),
            'submit_text' => fsan($_POST['form']['submit_text'] ?? ''),
            'feedback'    => fsan($_POST['form']['feedback'] ?? ''),
            'fields'      => $repeater('form_fields', ['id', 'label', 'tag', 'type', 'placeholder', 'rows', 'required'], function ($r) {
                $r['tag'] = f_allowed($r['tag'], ['input', 'textarea'], 'input');
                if ($r['id'] === '' || !preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $r['id'])) {
                    return null;
                }
                $field = [
                    'id'          => $r['id'],
                    'label'       => $r['label'],
                    'type'        => $r['tag'] === 'input'
                        ? f_allowed($r['type'], ['text', 'email', 'tel', 'url', 'number'], 'text') : null,
                    'placeholder' => $r['placeholder'],
                    'required'    => $r['required'] === '1',
                    'tag'         => $r['tag'],
                ];
                if ($r['tag'] === 'textarea') {
                    $field['rows'] = max(2, min(12, (int) ($r['rows'] ?: 4)));
                }
                return $field;
            }),
        ];
        if (count($form['fields']) < 1) {
            $errors[] = 'The offer form needs at least one valid field (id is required per row).';
        }

        // --- footer ---------------------------------------------------------
        $badges = array_values(array_filter(
            $repeater('badges', ['value'], fn($r) => $r['value'] !== '' ? $r['value'] : null),
            fn($b) => $b !== null && $b !== ''
        ));
        $footer = [
            'text'   => fsan($_POST['footer']['text'] ?? ''),
            'badges' => $badges,
            'year'   => max(1970, min(2999, (int) ($_POST['footer']['year'] ?? date('Y')))),
        ];

        if (!$errors) {
            $data = [
                'meta'         => $meta,
                'brand'        => $brand,
                'contact'      => $contact,
                'nav_links'    => $nav_links,
                'hero_actions' => $hero_actions,
                'contact_info' => $contact_info,
                'benefits'     => $benefits,
                'form'         => $form,
                'footer'       => $footer,
            ];
            write_data_file($data);
            $saved = true;
        }
    }
}

// ---------------------------------------------------------------------------
// View helpers
// ---------------------------------------------------------------------------

function e(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function render_input(array $attrs): string
{
    $out = '<input';
    foreach ($attrs as $k => $v) {
        if ($v === null || $v === false) {
            continue;
        }
        if ($k === 'checked') {
            $out .= $v ? ' checked' : '';
            continue;
        }
        $out .= ' ' . $k . '="' . e((string) $v) . '"';
    }
    return $out . '>';
}

/**
 * $types: text | textarea | select | checkbox | hidden
 */
function field(string $label, string $type, string $name, $value = '', array $opts = []): string
{
    $value = is_array($value) ? '' : (string) $value;
    $html  = '<div class="fld">';
    if ($type !== 'hidden') {
        $html .= '<label>' . e($label) . (!empty($opts['hint']) ? '<span>' . e($opts['hint']) . '</span>' : '') . '</label>';
    }
    switch ($type) {
        case 'textarea':
            $html .= '<textarea name="' . e($name) . '" rows="' . (int) ($opts['rows'] ?? 3) . '">' . e((string) $value) . '</textarea>';
            break;
        case 'select':
            $html .= '<select name="' . e($name) . '">';
            foreach (($opts['options'] ?? []) as $key => $shown) {
                $sel = ((string) $value === (string) $key) ? ' selected' : '';
                $html .= '<option value="' . e((string) $key) . '"' . $sel . '>' . e($shown) . '</option>';
            }
            $html .= '</select>';
            break;
        case 'checkbox':
            $html .= '<label class="chk">' . render_input(['type' => 'checkbox', 'name' => $name, 'value' => '1', 'checked' => (bool) $value]) . e($label) . '</label>';
            break;
        case 'hidden':
            $html .= render_input(['type' => 'hidden', 'name' => $name, 'value' => $value]);
            break;
        default:
            $html .= render_input(['type' => $type, 'name' => $name, 'value' => $value] + array_intersect_key($opts, ['required' => 1]));
    }
    return $html . '</div>';
}

$heroTypes  = ['whatsapp' => 'WhatsApp button', 'call' => 'Call button'];
$fieldTags  = ['input' => 'Input', 'textarea' => 'Textarea'];
$fieldTypes = ['text' => 'text', 'email' => 'email', 'tel' => 'tel', 'url' => 'url', 'number' => 'number'];
$colors     = array_fill_keys(['indigo', 'purple', 'emerald', 'amber', 'rose', 'sky', 'teal', 'cyan', 'violet', 'fuchsia'], null);
$colorOpts  = array_combine(array_keys($colors), array_map('ucfirst', array_keys($colors)));
$backups    = list_backups();
$lastSaved  = is_file(DATA_FILE) ? date('Y-m-d H:i', filemtime(DATA_FILE)) : 'never';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · <?= e($data['meta']['title'] ?? 'Site data') ?></title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<header class="bar">
    <h1>Site Content Admin</h1>
    <div class="meta">data.php last saved: <strong><?= e($lastSaved) ?></strong></div>
    <div class="actions">
        <a class="btn ghost" href="index.php" target="_blank">View site ↗</a>
        <button type="submit" form="adminForm" class="btn primary">Save changes</button>
    </div>
</header>

<main>
    <?php if ($saved): ?>
        <p class="flash ok">Saved ✔ app/data.php updated and a backup written to app/backups/.</p>
    <?php endif; ?>
    <?php if ($restored !== ''): ?>
        <p class="flash ok">Restored backup <strong><?= e($restored) ?></strong>.</p>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
        <p class="flash bad"><?= e($err) ?></p>
    <?php endforeach; ?>

    <nav class="tabs" role="tablist">
        <button type="button" class="tab is-active" data-tab="general">General</button>
        <button type="button" class="tab" data-tab="layout">Layout</button>
        <button type="button" class="tab" data-tab="benefits">Benefits</button>
        <button type="button" class="tab" data-tab="form">Offer Form</button>
        <button type="button" class="tab" data-tab="footer">Footer</button>
        <?php if ($backups): ?>
        <button type="button" class="tab" data-tab="backup">Backup</button>
        <?php endif; ?>
    </nav>

    <form id="adminForm" method="post" action="admin.php">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <div class="tab-panel is-active" data-panel="general">
        <section class="card">
            <h2>Site / Meta</h2>
            <div class="grid">
                <?= field('Page title', 'text', 'meta[title]', $data['meta']['title'] ?? '', ['required' => 'required']) ?>
                <?= field('Language code', 'text', 'meta[language]', $data['meta']['language'] ?? 'en', ['hint' => 'e.g. en, bd']) ?>
            </div>
        </section>

        <section class="card">
            <h2>Brand / Domain</h2>
            <div class="grid">
                <?= field('Logo HTML (nav)', 'text', 'brand[logo_html]', $data['brand']['logo_html'] ?? '') ?>
                <?= field('Domain name', 'text', 'brand[name]', $data['brand']['name'] ?? '') ?>
                <?= field('Extension', 'text', 'brand[extension]', $data['brand']['extension'] ?? '') ?>
                <?= field('Hero badge', 'text', 'brand[badge]', $data['brand']['badge'] ?? '') ?>
            </div>
            <?= field('Hero description', 'textarea', 'brand[hero_heading]', $data['brand']['hero_heading'] ?? '') ?>
            <p class="tip">Logo HTML is rendered as raw markup in the navbar (e.g. <code>&lt;span class="text-indigo-500"&gt;S&lt;/span&gt;.BD</code>).</p>
        </section>

        <section class="card">
            <h2>Contact Numbers</h2>
            <div class="grid">
                <?= field('Phone (display)', 'text', 'contact[phone_display]', $data['contact']['phone_display'] ?? '') ?>
                <?= field('Phone (raw, for tel: links)', 'text', 'contact[phone_raw]', $data['contact']['phone_raw'] ?? '') ?>
                <?= field('WhatsApp number (digits only)', 'text', 'contact[whatsapp_number]', $data['contact']['whatsapp_number'] ?? '') ?>
            </div>
            <?= field('WhatsApp pre-filled text', 'text', 'contact[whatsapp_text]', $data['contact']['whatsapp_text'] ?? '') ?>
        </section>
        </div>
        <!-- /general panel -->

        <div class="tab-panel" data-panel="layout">
        <section class="card">
            <h2>Navigation Links <button type="button" class="btn small add" data-target="#nav-links-body">+ Add</button></h2>
            <div class="counters" id="nav-links-counter"><?= field('', 'hidden', 'nav_links_count', count($data['nav_links'] ?? [])) ?></div>
            <div class="repeater" id="nav-links-body" data-prefix="nav_links">
                <?php foreach (array_values($data['nav_links'] ?? []) as $i => $row): ?>
                    <div class="row">
                        <?= field('Label', 'text', 'nav_links[' . $i . '][label]', $row['label'] ?? '') ?>
                        <?= field('Href', 'text', 'nav_links[' . $i . '][href]', $row['href'] ?? '') ?>
                        <button type="button" class="btn small remove">✕</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <template id="tpl-nav_links">
                <div class="row">
                    <?= field('Label', 'text', 'nav_links[__I__][label]') ?>
                    <?= field('Href', 'text', 'nav_links[__I__][href]') ?>
                    <button type="button" class="btn small remove">✕</button>
                </div>
            </template>
        </section>

        <section class="card">
            <h2>Buttons (hero + floating contacts) <button type="button" class="btn small add" data-target="#hero-actions-body">+ Add</button></h2>
            <div class="counters" id="hero-actions-counter"><?= field('', 'hidden', 'hero_actions_count', count($data['hero_actions'] ?? [])) ?></div>
            <div class="repeater" id="hero-actions-body" data-prefix="hero_actions">
                <?php foreach (array_values($data['hero_actions'] ?? []) as $i => $row): ?>
                    <div class="row">
                        <?= field('Type', 'select', 'hero_actions[' . $i . '][type]', $row['type'] ?? 'call', ['options' => $heroTypes]) ?>
                        <?= field('Label', 'text', 'hero_actions[' . $i . '][label]', $row['label'] ?? '') ?>
                        <button type="button" class="btn small remove">✕</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <template id="tpl-hero_actions">
                <div class="row">
                    <?= field('Type', 'select', 'hero_actions[__I__][type]', 'call', ['options' => $heroTypes]) ?>
                    <?= field('Label', 'text', 'hero_actions[__I__][label]') ?>
                    <button type="button" class="btn small remove">✕</button>
                </div>
            </template>
        </section>

        <section class="card">
            <h2>Contact Info Strip <button type="button" class="btn small add" data-target="#contact-info-body">+ Add</button></h2>
            <div class="counters" id="contact-info-counter"><?= field('', 'hidden', 'contact_info_count', count($data['contact_info'] ?? [])) ?></div>
            <div class="repeater" id="contact-info-body" data-prefix="contact_info">
                <?php foreach (array_values($data['contact_info'] ?? []) as $i => $row): ?>
                    <div class="row">
                        <?= field('Label', 'text', 'contact_info[' . $i . '][label]', $row['label'] ?? '') ?>
                        <?= field('Value', 'text', 'contact_info[' . $i . '][value]', $row['value'] ?? '') ?>
                        <button type="button" class="btn small remove">✕</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <template id="tpl-contact_info">
                <div class="row">
                    <?= field('Label', 'text', 'contact_info[__I__][label]') ?>
                    <?= field('Value', 'text', 'contact_info[__I__][value]') ?>
                    <button type="button" class="btn small remove">✕</button>
                </div>
            </template>
        </section>
        </div>
        <!-- /layout panel -->

        <div class="tab-panel" data-panel="benefits">
        <section class="card">
            <h2>Benefit Cards <button type="button" class="btn small add" data-target="#benefits-body">+ Add card</button></h2>
            <div class="counters" id="benefits-counter"><?= field('', 'hidden', 'benefits_count', count($data['benefits'] ?? [])) ?></div>
            <div class="repeater" id="benefits-body" data-prefix="benefits">
                <?php foreach (array_values($data['benefits'] ?? []) as $i => $row): ?>
                    <div class="row">
                        <div class="row-top">
                            <?= field('Accent color', 'select', 'benefits[' . $i . '][color]', $row['color'] ?? 'indigo', ['options' => $colorOpts]) ?>
                            <?= field('Title', 'text', 'benefits[' . $i . '][title]', $row['title'] ?? '') ?>
                        </div>
                        <?= field('Description', 'textarea', 'benefits[' . $i . '][text]', $row['text'] ?? '') ?>
                        <?= field('SVG icon path (d attribute)', 'textarea', 'benefits[' . $i . '][icon_d]', $row['icon_d'] ?? '', ['rows' => 2, 'hint' => ' from heroicons.com or similar']) ?>
                        <button type="button" class="btn small remove">✕ Remove card</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <template id="tpl-benefits">
                <div class="row">
                    <div class="row-top">
                        <?= field('Accent color', 'select', 'benefits[__I__][color]', 'indigo', ['options' => $colorOpts]) ?>
                        <?= field('Title', 'text', 'benefits[__I__][title]') ?>
                    </div>
                    <?= field('Description', 'textarea', 'benefits[__I__][text]') ?>
                    <?= field('SVG icon path (d attribute)', 'textarea', 'benefits[__I__][icon_d]', '', ['rows' => 2]) ?>
                    <button type="button" class="btn small remove">✕ Remove card</button>
                </div>
            </template>
        </section>
        </div>
        <!-- /benefits panel -->

        <div class="tab-panel" data-panel="form">
        <section class="card">
            <h2>Offer Form</h2>
            <div class="grid">
                <?= field('Section title', 'text', 'form[title]', $data['form']['title'] ?? '') ?>
                <?= field('Section subtitle', 'text', 'form[subtitle]', $data['form']['subtitle'] ?? '') ?>
                <?= field('Submit button text', 'text', 'form[submit_text]', $data['form']['submit_text'] ?? '') ?>
                <?= field('Feedback message', 'text', 'form[feedback]', $data['form']['feedback'] ?? '') ?>
            </div>
            <h3>Fields <button type="button" class="btn small add" data-target="#form-fields-body">+ Add field</button></h3>
            <p class="tip">Keep the ids <code>name</code>, <code>email</code>, <code>amount</code>, <code>message</code> — assets/js/main.js builds the WhatsApp message from them.</p>
            <div class="counters" id="form-fields-counter"><?= field('', 'hidden', 'form_fields_count', count($data['form']['fields'] ?? [])) ?></div>
            <div class="repeater" id="form-fields-body" data-prefix="form_fields">
                <?php foreach (array_values($data['form']['fields'] ?? []) as $i => $row): ?>
                    <div class="row">
                        <div class="row-top">
                            <?= field('ID', 'text', 'form_fields[' . $i . '][id]', $row['id'] ?? '') ?>
                            <?= field('Label', 'text', 'form_fields[' . $i . '][label]', $row['label'] ?? '') ?>
                            <?= field('Tag', 'select', 'form_fields[' . $i . '][tag]', $row['tag'] ?? 'input', ['options' => $fieldTags]) ?>
                            <?= field('Input type', 'select', 'form_fields[' . $i . '][type]', $row['type'] ?? 'text', ['options' => $fieldTypes]) ?>
                        </div>
                        <div class="row-top">
                            <?= field('Placeholder', 'text', 'form_fields[' . $i . '][placeholder]', $row['placeholder'] ?? '') ?>
                            <?= field('Textarea rows', 'text', 'form_fields[' . $i . '][rows]', $row['rows'] ?? '4', ['hint' => 'textarea only']) ?>
                            <?= field('Required', 'checkbox', 'form_fields[' . $i . '][required]', $row['required'] ?? false) ?>
                        </div>
                        <button type="button" class="btn small remove">✕ Remove field</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <template id="tpl-form_fields">
                <div class="row">
                    <div class="row-top">
                        <?= field('ID', 'text', 'form_fields[__I__][id]') ?>
                        <?= field('Label', 'text', 'form_fields[__I__][label]') ?>
                        <?= field('Tag', 'select', 'form_fields[__I__][tag]', 'input', ['options' => $fieldTags]) ?>
                        <?= field('Input type', 'select', 'form_fields[__I__][type]', 'text', ['options' => $fieldTypes]) ?>
                    </div>
                    <div class="row-top">
                        <?= field('Placeholder', 'text', 'form_fields[__I__][placeholder]') ?>
                        <?= field('Textarea rows', 'text', 'form_fields[__I__][rows]', '4') ?>
                        <?= field('Required', 'checkbox', 'form_fields[__I__][required]', false) ?>
                    </div>
                    <button type="button" class="btn small remove">✕ Remove field</button>
                </div>
            </template>
        </section>
        </div>
        <!-- /form panel -->

        <div class="tab-panel" data-panel="footer">
        <section class="card">
            <h2>Footer</h2>
            <?= field('Footer text (phone is appended automatically)', 'textarea', 'footer[text]', $data['footer']['text'] ?? '') ?>
            <div class="grid">
                <?= field('Copyright year', 'text', 'footer[year]', (string) ($data['footer']['year'] ?? date('Y'))) ?>
            </div>
            <h3>Badges <button type="button" class="btn small add" data-target="#badges-body">+ Add badge</button></h3>
            <div class="counters" id="badges-counter"><?= field('', 'hidden', 'badges_count', count($data['footer']['badges'] ?? [])) ?></div>
            <div class="repeater" id="badges-body" data-prefix="badges">
                <?php foreach (array_values($data['footer']['badges'] ?? []) as $i => $badge): ?>
                    <div class="row">
                        <?= field('Badge', 'text', 'badges[' . $i . '][value]', $badge) ?>
                        <button type="button" class="btn small remove">✕</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <template id="tpl-badges">
                <div class="row">
                    <?= field('Badge', 'text', 'badges[__I__][value]') ?>
                    <button type="button" class="btn small remove">✕</button>
                </div>
            </template>
        </section>
        </div>
        <!-- /footer panel -->

        <div class="bar bottom" id="saveBar">
            <button type="submit" class="btn primary">Save changes</button>
            <a class="btn ghost" href="index.php" target="_blank">View site ↗</a>
        </div>
    </form>

    <?php if ($backups): ?>
        <div class="tab-panel" data-panel="backup">
        <section class="card">
            <h2>Restore backup</h2>
            <p class="tip">Each save copies the previous data.php into app/backups/. Restoring overwrites the current data (the current version is backed up first only if you save again — check the list).</p>
            <form method="post" action="admin.php" class="rowform">
                <input type="hidden" name="action" value="restore">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <select name="backup">
                    <?php foreach ($backups as $b): ?>
                        <option value="<?= e($b) ?>"><?= e(basename($b)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn warn confirm-restore">Restore</button>
            </form>
        </section>
        </div>
    <?php endif; ?>
</main>

<script src="assets/js/admin.js"></script>
</body>
</html>
