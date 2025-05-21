<?php if(isset($paginacion) && $paginacion->total_registros > 0): ?>
    <?php echo $paginacion->paginacion(); ?>
<?php endif; ?>