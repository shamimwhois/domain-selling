<?php
$data = require __DIR__ . '/app/data.php';

$meta     = $data['meta'];
$brand    = $data['brand'];
$contact  = $data['contact'];
$form     = $data['form'];
$telLink  = 'tel:' . $contact['phone_raw'];
$waLink   = 'https://wa.me/' . $contact['whatsapp_number'] . '?text=' . urlencode($contact['whatsapp_text']);

// Inline icon geometry (authored SVG, one consistent stroke weight)
$iconPhone    = 'M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z';
$iconWhatsapp = 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.94 3.659 1.437 5.634 1.437h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z';

// Build the tab model from nav_links; each link maps to a panel keyed by its href
$tabId = function (string $href): string { return ltrim($href, '#'); };
$panels = [
    'benefits' => 'The Asset',
    'offer'    => 'Place Your Offer',
];
$activeFirst = $data['nav_links'][0]['href'] ?? '#benefits';
$activeFirstId = $tabId($activeFirst);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($meta['language']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($brand['hero_heading']) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- ===================== Masthead ===================== -->
    <header class="masthead">
        <div class="wrap masthead__inner">
            <a class="monogram" href="#top" aria-label="<?= htmlspecialchars($brand['name'] . $brand['extension']) ?>">
                <?= $brand['logo_html'] ?>
            </a>

            <nav class="tabnav" aria-label="Catalogue sections">
                <div class="tablist" role="tablist" aria-label="Catalogue sections">
                    <?php foreach ($data['nav_links'] as $link):
                        $id = $tabId($link['href']);
                        if (!isset($panels[$id])) { continue; } ?>
                        <button class="tab" role="tab" type="button"
                                id="tab-<?= htmlspecialchars($id) ?>"
                                aria-controls="panel-<?= htmlspecialchars($id) ?>"
                                aria-selected="<?= $id === $activeFirstId ? 'true' : 'false' ?>"
                                tabindex="<?= $id === $activeFirstId ? '0' : '-1' ?>"
                                data-target="<?= htmlspecialchars($id) ?>">
                            <?= htmlspecialchars($link['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </nav>

            <a class="masthead__cta" href="<?= $telLink ?>" aria-label="Call <?= htmlspecialchars($contact['phone_display']) ?>">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= $iconPhone ?>"/></svg>
                <span><?= htmlspecialchars($contact['phone_display']) ?></span>
            </a>
        </div>
    </header>

    <main id="top">

        <!-- ===================== Lot record ===================== -->
        <section class="lot">
            <div class="wrap lot__grid">
                <div class="lot__entry">
                    <div class="lot__meta smallcaps">
                        <span class="ref">Lot 001 &nbsp;·&nbsp; A Rare .BD Keyword</span>
                        <span class="status">Open for Offers</span>
                    </div>

                    <h1 class="lot__title">
                        <span class="display"><?= htmlspecialchars($brand['name']) ?></span><span class="ext"><?= htmlspecialchars($brand['extension']) ?></span>
                    </h1>
                    <div class="lot__rule" aria-hidden="true"></div>
                    <p class="lot__desc"><?= htmlspecialchars($brand['hero_heading']) ?></p>

                    <div class="estimate">
                        <?php foreach ($data['contact_info'] as $info): ?>
                            <div class="estimate__cell">
                                <span class="k smallcaps"><?= htmlspecialchars($info['label']) ?></span>
                                <span class="v"><?= htmlspecialchars($info['value']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="lot__actions">
                        <?php foreach ($data['hero_actions'] as $action): ?>
                            <?php if ($action['type'] === 'whatsapp'): ?>
                                <a class="btn btn--primary" href="<?= $waLink ?>" target="_blank" rel="noopener">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= $iconWhatsapp ?>"/></svg>
                                    <?= htmlspecialchars($action['label']) ?>
                                </a>
                            <?php elseif ($action['type'] === 'call'): ?>
                                <a class="btn btn--ghost" href="<?= $telLink ?>">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= $iconPhone ?>"/></svg>
                                    <?= htmlspecialchars($action['label']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Certificate of ownership (guilloché) -->
                <figure class="cert" aria-label="Certificate of ownership for <?= htmlspecialchars($brand['name'] . $brand['extension']) ?>">
                    <svg class="cert__guilloche" viewBox="0 0 200 250" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                        <g fill="none" stroke="currentColor" stroke-width="0.4">
                            <?php for ($i = 0; $i < 26; $i++): ?>
                                <ellipse cx="100" cy="125" rx="<?= 96 - $i * 3 ?>" ry="<?= 120 - $i * 3.7 ?>" transform="rotate(<?= $i * 7 ?> 100 125)"/>
                            <?php endfor; ?>
                        </g>
                    </svg>
                    <div class="cert__inner">
                        <div class="cert__label smallcaps">Certificate of Title</div>
                        <div class="cert__name"><span class="display"><?= htmlspecialchars($brand['name']) ?></span><span class="ext"><?= htmlspecialchars($brand['extension']) ?></span></div>
                        <div class="cert__stamp"><?= htmlspecialchars($brand['badge']) ?></div>
                    </div>
                    <svg class="cert__seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </figure>
            </div>
        </section>

        <!-- ===================== Tab panels ===================== -->
        <div class="panel-region">
            <div class="wrap">

                <!-- Benefits panel -->
                <section class="panel reveal" id="panel-benefits" role="tabpanel" aria-labelledby="tab-benefits" <?= $activeFirstId === 'benefits' ? 'data-active="true"' : 'hidden' ?>>
                    <div class="panel__head">
                        <h2><?= htmlspecialchars($panels['benefits']) ?></h2>
                        <span class="index smallcaps">Particulars of the Lot</span>
                    </div>
                    <div class="benefits">
                        <?php foreach ($data['benefits'] as $i => $benefit): ?>
                            <article class="particular">
                                <div class="particular__idx"><?= str_pad((string)($i + 1), 3, '0', STR_PAD_LEFT) ?></div>
                                <div class="particular__icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="<?= htmlspecialchars($benefit['icon_d']) ?>"/></svg>
                                </div>
                                <h3><?= htmlspecialchars($benefit['title']) ?></h3>
                                <p><?= htmlspecialchars($benefit['text']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Enquire panel (contact + offer form) -->
                <section class="panel reveal" id="panel-offer" role="tabpanel" aria-labelledby="tab-offer" <?= $activeFirstId === 'offer' ? 'data-active="true"' : 'hidden' ?>>
                    <div class="panel__head">
                        <h2><?= htmlspecialchars($form['title']) ?></h2>
                        <span class="index smallcaps">Registration</span>
                    </div>

                    <div class="enquire">
                        <aside class="enquire__aside">
                            <p class="lead"><?= htmlspecialchars($form['subtitle']) ?></p>
                            <div class="contact-list">
                                <?php foreach ($data['contact_info'] as $info): ?>
                                    <div class="contact-list__row">
                                        <span class="k smallcaps"><?= htmlspecialchars($info['label']) ?></span>
                                        <span class="v"><?= htmlspecialchars($info['value']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </aside>

                        <form class="offer" id="contactForm" novalidate>
                            <div class="offer__grid">
                                <?php foreach ($form['fields'] as $idx => $field):
                                    $isFull = ($field['tag'] === 'textarea') || ($idx === count($form['fields']) - 1) || ($idx === 2); ?>
                                    <div class="field<?= $isFull ? ' field--full' : '' ?>">
                                        <label for="<?= htmlspecialchars($field['id']) ?>"><?= htmlspecialchars($field['label']) ?></label>
                                        <?php if ($field['tag'] === 'textarea'): ?>
                                            <textarea id="<?= htmlspecialchars($field['id']) ?>" name="<?= htmlspecialchars($field['id']) ?>" rows="<?= (int) $field['rows'] ?>" placeholder="<?= htmlspecialchars($field['placeholder']) ?>"<?= $field['required'] ? ' required' : '' ?>></textarea>
                                        <?php else: ?>
                                            <input id="<?= htmlspecialchars($field['id']) ?>" name="<?= htmlspecialchars($field['id']) ?>" type="<?= htmlspecialchars($field['type'] ?: 'text') ?>" placeholder="<?= htmlspecialchars($field['placeholder']) ?>"<?= $field['required'] ? ' required' : '' ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" class="btn btn--primary" onclick="handleSubmit()">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= $iconWhatsapp ?>"/></svg>
                                <?= htmlspecialchars($form['submit_text']) ?>
                            </button>

                            <div id="feedback" class="offer__feedback" hidden><?= htmlspecialchars($form['feedback']) ?></div>
                        </form>
                    </div>
                </section>

            </div>
        </div>
    </main>

    <!-- ===================== Colophon ===================== -->
    <footer class="colophon">
        <div class="wrap">
            <div class="colophon__name"><span class="display"><?= htmlspecialchars($brand['name']) ?></span><span class="ext"><?= htmlspecialchars($brand['extension']) ?></span></div>
            <p class="colophon__note">
                <?= htmlspecialchars($data['footer']['text']) ?> <span class="num"><?= htmlspecialchars($contact['phone_display']) ?></span>.
            </p>
            <div class="colophon__marks smallcaps">
                <span>&copy; <?= (int) $data['footer']['year'] ?></span>
                <?php foreach ($data['footer']['badges'] as $badge): ?>
                    <span><?= htmlspecialchars($badge) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </footer>

    <!-- Floating offers -->
    <div class="float">
        <?php foreach ($data['hero_actions'] as $action): ?>
            <?php if ($action['type'] === 'whatsapp'): ?>
                <a class="wa" href="<?= $waLink ?>" target="_blank" rel="noopener" aria-label="<?= htmlspecialchars($action['label']) ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= $iconWhatsapp ?>"/></svg>
                </a>
            <?php elseif ($action['type'] === 'call'): ?>
                <a class="call" href="<?= $telLink ?>" aria-label="<?= htmlspecialchars($action['label']) ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= $iconPhone ?>"/></svg>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
