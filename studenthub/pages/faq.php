<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'Frequently Asked Questions (FAQ) - StudentHub';
$activePage = 'faq';
$breadcrumbs = ['FAQ' => ''];

// Load FAQs from data/faqs.json (Practical 6)
$faqsJsonFile = __DIR__ . '/../data/faqs.json';
$faqs = [];
if (file_exists($faqsJsonFile)) {
    $faqs = json_decode(file_get_contents($faqsJsonFile), true) ?? [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:880px; padding-top:30px; margin-bottom:70px;">
  <div class="section-header" style="text-align:left;">
    <span class="badge badge-primary">Practical 4: Interactive Collapsible Accordion</span>
    <h1 style="font-size:2.4rem; font-weight:800; margin-top:8px; margin-bottom:8px;">Frequently Asked Questions</h1>
    <p style="color:var(--text-muted); font-size:1.05rem;">
      Find fast answers to common questions about StudentHub portal features, registrations, account security, and lab practicals.
    </p>
  </div>

  <!-- Live FAQ Search Filter -->
  <div style="margin-bottom:28px;">
    <input type="text" id="faqSearchInput" class="filter-input" placeholder="🔍 Type to search FAQ questions or topics in real time..." style="width:100%; font-size:1.05rem; padding:14px 18px;">
  </div>

  <!-- Collapsible FAQ Accordion List (Practical 4) -->
  <div class="faq-list" id="faqAccordionList">
    <?php foreach ($faqs as $index => $faq): ?>
      <div class="faq-item <?= $index === 0 ? 'active' : '' ?>" data-category="<?= htmlspecialchars(strtolower($faq['category'])) ?>">
        <button type="button" class="faq-question" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
          <span>
            <span class="badge badge-neutral" style="margin-right:8px;"><?= htmlspecialchars($faq['category']) ?></span>
            <?= htmlspecialchars($faq['question']) ?>
          </span>
          <span class="faq-icon">+</span>
        </button>
        <div class="faq-answer">
          <p><?= htmlspecialchars($faq['answer']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:28px; text-align:center; margin-top:40px;">
    <h3>Still have questions?</h3>
    <p style="color:var(--text-muted); margin:8px 0 18px;">Our faculty coordinators and laboratory staff are here to help you.</p>
    <a href="contact.php" class="btn btn-primary">Contact Support &rarr;</a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('faqSearchInput');
  const items = document.querySelectorAll('.faq-item');

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.toLowerCase().trim();
      items.forEach((item) => {
        const text = item.textContent.toLowerCase();
        if (text.includes(q)) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
