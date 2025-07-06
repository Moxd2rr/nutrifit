    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3><?php echo APP_NAME; ?></h3>
                <p>Votre partenaire pour une vie plus saine et équilibrée. Programmes personnalisés, nutrition adaptée et suivi expert.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class='bx bxl-facebook'></i></a>
                    <a href="#" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
                    <a href="#" aria-label="Twitter"><i class='bx bxl-twitter'></i></a>
                    <a href="#" aria-label="YouTube"><i class='bx bxl-youtube'></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Services</h4>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>programs/">Programmes d'entraînement</a></li>
                    <li><a href="<?php echo APP_URL; ?>nutrition/">Plans nutritionnels</a></li>
                    <li><a href="<?php echo APP_URL; ?>coaching/">Coaching personnalisé</a></li>
                    <li><a href="<?php echo APP_URL; ?>shop/">Boutique</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>help/">Centre d'aide</a></li>
                    <li><a href="<?php echo APP_URL; ?>contact/">Contact</a></li>
                    <li><a href="<?php echo APP_URL; ?>faq/">FAQ</a></li>
                    <li><a href="<?php echo APP_URL; ?>support/">Support technique</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Légal</h4>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>terms/">Conditions d'utilisation</a></li>
                    <li><a href="<?php echo APP_URL; ?>privacy/">Politique de confidentialité</a></li>
                    <li><a href="<?php echo APP_URL; ?>cookies/">Cookies</a></li>
                    <li><a href="<?php echo APP_URL; ?>legal/">Mentions légales</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Tous droits réservés.</p>
                <p>Version <?php echo APP_VERSION; ?></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo APP_URL; ?>assets/js/main.js"></script>
    
    <!-- Scripts spécifiques à la page -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Google Analytics (optionnel) -->
    <script>
        // Code Google Analytics ici si nécessaire
    </script>
</body>
</html> 