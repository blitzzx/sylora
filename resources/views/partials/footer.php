</div>
<?php if (!$isPjax): ?>
<?php if (empty($noFooter)): ?>
<footer class="site-footer">
  <div class="container footer-inner">
    <div class="footer-brand">
      <img src="/assets/img/Logo-Sylora.png" alt="Sylora" height="32" loading="lazy">
    </div>
    <p class="footer-copy">© <?php echo date('Y'); ?> Sylora. <span data-i18n-html="footer.credit"><?= t('footer.credit') ?></span></p>
    <div class="footer-links">
      <a href="/historia" data-i18n="nav.historia"><?= t('nav.historia') ?></a>
      <a href="/jogar" data-i18n="nav.play"><?= t('nav.play') ?></a>
      <?php if ($isLoggedIn): ?>
      <a href="/sobre#contacto" data-i18n="footer.contact"><?= t('footer.contact') ?></a>
      <?php endif; ?>
    </div>
  </div>
</footer>
<?php endif; ?>
<?php foreach (['core', 'ui', 'avatar', 'saves'] as $jsFile): ?>
<script src="/js/<?= $jsFile ?>.js?v=<?= @filemtime(ROOT . '/public/js/' . $jsFile . '.js') ?: '1' ?>"></script>
<?php endforeach; ?>
</body>
</html>
<?php endif; ?>
