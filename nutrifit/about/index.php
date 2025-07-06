<?php
$page_title = "À Propos";
$page_description = "Découvrez NutriFit, notre mission, nos valeurs et notre équipe.";

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container content-section">
    <div class="about-hero">
        <h1>À Propos de NutriFit</h1>
        <p class="lead">Votre partenaire pour une vie saine et équilibrée.</p>
    </div>

    <section class="about-section">
        <h2>Notre Mission</h2>
        <p>Chez NutriFit, notre mission est de vous accompagner vers vos objectifs de bien-être en vous offrant des plans de fitness et de nutrition personnalisés, accessibles et adaptés à votre mode de vie. Nous croyons que la santé est une priorité et qu'avec le bon soutien, chacun peut atteindre son plein potentiel.</p>
        <p>Nous nous engageons à fournir des outils innovants, des conseils d'experts et une communauté motivante pour faire de votre parcours de transformation une réussite durable.</p>
    </section>

    <section class="about-section">
        <h2>Nos Valeurs</h2>
        <div class="values-grid">
            <div class="value-card">
                <i class='bx bx-heart'></i>
                <h3>Passion</h3>
                <p>Nous sommes passionnés par la santé et le fitness, et nous voulons partager cette passion avec vous.</p>
            </div>
            <div class="value-card">
                <i class='bx bx-brain'></i>
                <h3>Expertise</h3>
                <p>Nos programmes sont basés sur des connaissances scientifiques et l'expérience de professionnels qualifiés.</p>
            </div>
            <div class="value-card">
                <i class='bx bx-group'></i>
                <h3>Communauté</h3>
                <p>Nous construisons une communauté solidaire où chacun se sent soutenu et inspiré.</p>
            </div>
            <div class="value-card">
                <i class='bx bx-award'></i>
                <h3>Engagement</h3>
                <p>Votre succès est notre priorité. Nous nous engageons à vous aider à atteindre vos objectifs.</p>
            </div>
        </div>
    </section>

    <section class="about-section">
        <h2>Notre Équipe</h2>
        <p>NutriFit est composée d'une équipe dévouée d'experts en nutrition, d'entraîneurs sportifs certifiés et de développeurs passionnés. Nous travaillons ensemble pour créer la meilleure expérience possible pour nos utilisateurs.</p>
        <div class="team-members">
            <div class="team-member">
                <img src="<?php echo APP_URL; ?>assets/images/team/placeholder-male.jpg" alt="Nom Membre 1">
                <h3>Nom Membre 1</h3>
                <p>Fondateur & CEO</p>
            </div>
            <div class="team-member">
                <img src="<?php echo APP_URL; ?>assets/images/team/placeholder-female.jpg" alt="Nom Membre 2">
                <h3>Nom Membre 2</h3>
                <p>Chef Nutritionniste</p>
            </div>
            <div class="team-member">
                <img src="<?php echo APP_URL; ?>assets/images/team/placeholder-male.jpg" alt="Nom Membre 3">
                <h3>Nom Membre 3</h3>
                <p>Directeur des Programmes Fitness</p>
            </div>
        </div>
    </section>

    <section class="about-section">
        <h2>Contactez-nous</h2>
        <p>Des questions, des suggestions, ou simplement envie de nous dire bonjour ? N'hésitez pas à nous contacter !</p>
        <p>Email: <a href="mailto:contact@nutrifit.com">contact@nutrifit.com</a></p>
        <p>Suivez-nous sur les réseaux sociaux :</p>
        <div class="social-links">
            <a href="#"><i class='bx bxl-facebook-circle'></i></a>
            <a href="#"><i class='bx bxl-instagram-alt'></i></a>
            <a href="#"><i class='bx bxl-twitter'></i></a>
            <a href="#"><i class='bx bxl-linkedin-square'></i></a>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?> 