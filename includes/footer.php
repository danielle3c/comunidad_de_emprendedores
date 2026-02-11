    </div><!-- end content-area -->
</div><!-- end main-content -->
</div><!-- end d-flex -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Confirmación de eliminación
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('¿Está seguro que desea eliminar este registro?')) e.preventDefault();
    });
});
</script>
</body>
</html>
