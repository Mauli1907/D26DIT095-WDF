  </main>

  <!-- Wireframe requirement: Footer: contact + social links -->
  <footer class="site-footer" role="contentinfo">
    <div class="container footer-grid">
      <div class="footer-col">
        <a href="index.php" class="brand-logo" style="margin-bottom:14px; display:inline-flex;">
          <img src="../assets/images/logo.svg" alt="StudentHub Logo" width="160" height="40">
        </a>
        <p style="color:var(--text-muted); font-size:0.925rem; margin-bottom:16px;">
          StudentHub is the unified digital campus ecosystem for Charotar University of Science and Technology (CHARUSAT), Faculty of Technology & Engineering (FTE).
        </p>
        <div class="social-links" aria-label="Social media links">
          <a href="https://github.com" class="social-link" title="GitHub" target="_blank" rel="noopener">🐙</a>
          <a href="https://linkedin.com" class="social-link" title="LinkedIn" target="_blank" rel="noopener">💼</a>
          <a href="https://twitter.com" class="social-link" title="Twitter" target="_blank" rel="noopener">🐦</a>
          <a href="https://charusat.ac.in" class="social-link" title="University Portal" target="_blank" rel="noopener">🏛️</a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul class="footer-links">
          <li><a href="index.php">Home Page</a></li>
          <li><a href="about.php">About Us & FTE</a></li>
          <li><a href="events.php">Campus Events</a></li>
          <li><a href="faq.php">Frequently Asked Questions</a></li>
          <li><a href="contact.php">Contact & Feedback</a></li>
          <li><a href="submissions_view.php">View Form Submissions</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Academic Lab Modules</h4>
        <ul class="footer-links">
          <li><a href="../practicals/index.php">🧪 All Practicals (2-15)</a></li>
          <li><a href="ajax_demo.php">⚡ Fetch API CRUD (Prac 13)</a></li>
          <li><a href="admin_students.php">👥 Student CRUD (Prac 11)</a></li>
          <li><a href="admin_events.php">📅 Event CRUD (Prac 12)</a></li>
          <li><a href="admin_audit.php">📜 Audit Logs (Prac 14)</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Campus Contact</h4>
        <p style="color:var(--text-muted); font-size:0.925rem; line-height:1.7;">
          📍 Faculty of Technology and Engineering (FTE)<br>
          Charotar University of Science and Technology (CHARUSAT)<br>
          Changa, Dist. Anand, Gujarat - 388421<br>
          📞 Phone: +91 2697 265011 / 265021<br>
          ✉️ Email: <a href="mailto:info.fte@charusat.ac.in">info.fte@charusat.ac.in</a>
        </p>
      </div>
    </div>

    <div class="container footer-bottom">
      <p>&copy; <?= date('Y') ?> CHARUSAT - Faculty of Technology and Engineering (FTE). Course: ITUE203 Web Development Frameworks.</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="../assets/js/main.js"></script>
  <?php if (isset($extraScript) && !empty($extraScript)): ?>
    <script src="<?= htmlspecialchars($extraScript) ?>"></script>
  <?php endif; ?>
</body>
</html>
