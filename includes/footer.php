</div>
<?php if (empty($_SERVER['HTTP_X_PJAX'])): ?>
<?php if (empty($noFooter)): ?>
<footer class="site-footer">
  <div class="container footer-inner">
    <div class="footer-brand">
      <img src="assets/img/Logo-Sylora.png" alt="Sylora" height="32" loading="lazy">
    </div>
    <p class="footer-copy">© <?php echo date('Y'); ?> Sylora. Developed by <a href="sobre.php">Márcio Sousa e Samuel Meixieira</a>.</p>
    <div class="footer-links">
      <a href="historia.php" data-i18n="nav.historia"><?= t('nav.historia') ?></a>
      <a href="jogar.php" data-i18n="nav.play"><?= t('nav.play') ?></a>
      <?php if (!empty($isLoggedIn)): ?>
      <a href="sobre.php#contacto" data-i18n="footer.contact"><?= t('footer.contact') ?></a>
      <?php endif; ?>
    </div>
  </div>
</footer>
<?php endif; ?>
<script src="js/main.js?v=<?php echo filemtime('js/main.js'); ?>"></script>
</body>
</html>
<?php endif; ?>
