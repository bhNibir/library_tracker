            </main>
        </div>
    </div>
    
    <script>
        // Highlight active menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const menuItems = document.querySelectorAll('nav a');
            
            menuItems.forEach(item => {
                if (currentLocation.includes(item.getAttribute('href').split('/').pop())) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 