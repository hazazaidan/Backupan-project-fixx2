<div class="toast-container" id="toast-container"></div>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?php if (isset($extraJs)): ?>
<script src="<?= BASE_URL ?>/assets/js/<?= $extraJs ?>.js"></script>
<?php endif; ?>
<script>lucide.createIcons();</script>
</body>
</html>