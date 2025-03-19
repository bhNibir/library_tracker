            </main>
            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-600">
                <div class="container">
                    <p><?php echo date('Y'); ?> 
                    <i class="fas fa-copyright mx-1 text-accent"></i>
                       <a href="https://www.linkedin.com/in/bh-nibir" target="_blank" class="text-primary hover:underline">
                       BH Nibir
                       </a> ||
                        Built with <span class="text-red-500"><i class="fas fa-heart"></i></span>
                    </p>
                </div>
            </footer>
        </div>
    </div>
    
    <script>
        // Highlight active menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const menuItems = document.querySelectorAll('nav a');
            
            menuItems.forEach(item => {
                const href = item.getAttribute('href');
                const hrefPath = href.split('/');
                const hrefPage = hrefPath[hrefPath.length - 2] + '/' + hrefPath[hrefPath.length - 1];
                
                // Special case for index.php (dashboard)
                if (href.includes('index.php') && (currentLocation.endsWith('/library_tracker/') || currentLocation.endsWith('index.php'))) {
                    item.classList.add('active');
                    return;
                }
                
                // Check if the current URL contains the link's path
                if (currentLocation.includes(hrefPage) && !href.includes('index.php')) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 