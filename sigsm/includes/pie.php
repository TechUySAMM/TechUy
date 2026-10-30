<?php
/* ============================================================
   includes/pie.php  (presentacion)
   Cierre comun de todas las paginas. $scripts permite que una
   pagina agregue sus propios .js de public/js.
   ============================================================ */
?>
</main>

<footer class="pie-sigsm">
    S.I.G.S.M. - Hospital de Clínicas &middot; TechUY &middot; Proyecto de Egreso ISBO 2026
    <br><small>Sistema con datos y documentos ficticios</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="<?= URL_BASE ?>/public/js/confirmar.js"></script>
<?php foreach ($scripts ?? [] as $js): ?>
<script src="<?= URL_BASE ?>/public/js/<?= e($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
