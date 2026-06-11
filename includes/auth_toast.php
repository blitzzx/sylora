<?php
/* Toast/popup para páginas de auth standalone (login, forgot) — reutiliza as classes
   #sylora-toast já definidas no style.css. Define ANTES de incluir este partial:
     $authToastMsg      (string)  mensagem a mostrar
     $authToastType     (string)  'info' | 'success' | 'error'  (default 'info')
     $authToastRedirect (string)  URL para onde reencaminhar após o delay (opcional)
     $authToastDelay    (int)     ms antes do redirect (default 2500) */
?>
<div id="sylora-toast" aria-live="polite" aria-atomic="true"></div>
<script>
function showToast(msg, type){
  var t = document.getElementById('sylora-toast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'sylora-toast-show sylora-toast-' + (type || 'info');
  clearTimeout(t._timer);
  t._timer = setTimeout(function(){ t.className = ''; }, 3800);
}
<?php if (!empty($authToastMsg)): ?>
(function(){
  var msg      = <?= json_encode($authToastMsg) ?>;
  var type     = <?= json_encode($authToastType ?? 'info') ?>;
  var redirect = <?= json_encode($authToastRedirect ?? '') ?>;
  var delay    = <?= (int)($authToastDelay ?? 2500) ?>;
  setTimeout(function(){ showToast(msg, type); }, 120);
  if (redirect) { setTimeout(function(){ window.location.href = redirect; }, delay); }
})();
<?php endif; ?>
</script>
