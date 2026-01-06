    </main>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light">
        <div class="container-fluid py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-light mb-0">
                        &copy; <?php echo date('Y'); ?> Inventory Management System.
                        All rights reserved.
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-light mb-0">
                        <i class="bi bi-clock me-1"></i>
                        Version 1.0.0 | Last updated: <?php echo date('M j, Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const pageContainer = document.querySelector('.page-container');

            // Toggle sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('collapsed');
                
                // Only show overlay on mobile
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                } else {
                    // On desktop, adjust page container margin when collapsed
                    if (sidebar.classList.contains('collapsed')) {
                        pageContainer.style.marginLeft = '0';
                    } else {
                        pageContainer.style.marginLeft = 'var(--sidebar-width)';
                    }
                }
            }

            // Event listeners
            sidebarToggle.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);

            // Close sidebar on mobile when clicking a nav link
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        toggleSidebar();
                    }
                });
            });

            // Initialize margins on page load
            if (window.innerWidth > 768) {
                if (sidebar.classList.contains('collapsed')) {
                    pageContainer.style.marginLeft = '0';
                } else {
                    pageContainer.style.marginLeft = 'var(--sidebar-width)';
                }
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    // On desktop, check if sidebar is collapsed and set margins accordingly
                    if (sidebar.classList.contains('collapsed')) {
                        pageContainer.style.marginLeft = '0';
                    } else {
                        pageContainer.style.marginLeft = 'var(--sidebar-width)';
                    }
                } else {
                    // On mobile, reset margins
                    pageContainer.style.marginLeft = '0';
                }
            });

            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.classList.contains('fade')) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    } else {
                        alert.style.display = 'none';
                    }
                }, 5000);
            });
        });
    </script>
</body>
</html>