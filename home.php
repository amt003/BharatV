<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BharatV - Voting Made Simple</title>
    <link rel="stylesheet" href="css/home.css">

  </head>
  <body>
    <header>
      <div class="navbar">
        <div class="company-logo">
          <img src="assets/logo.jpg" alt="Company logo">
        </div>
        <nav>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#how-it-works">How It Works</a></li>
            <li><a href="#contact">Contact Us</a></li>

          </ul>
        </nav>
        <div class="auth-buttons">
          <button class="register" onclick="register()">Register To Vote</button>
        </div>
      </div>
    </header>
    <section class="hero">
    <div class="slider">
      <div class="slide active" >
        <div class="hero-content">
          <h1>Welcome to BharatV</h1>
          <p>A secure and transparent platform redefining the voting process for a better democracy.
          BharatV is a digital platform designed to automate the voting process securely and efficiently.</p>
          <div class="hero-buttons">
            <button class="cta-primary" onclick="login()">Start Voting</button>
            
          </div>
        </div>
      </div>
      <div class="slide" >
        <div class="hero-content">
          <h1>Secure Digital Voting</h1>
          <p>Experience the future of democracy with our state-of-the-art voting platform.</p>
          <div class="hero-buttons">
            <button class="cta-primary" onclick="login()">Start Voting</button>
  
          </div>
        </div>
      </div>
      <div class="slide" >
        <div class="hero-content">
          <h1>Your Vote, Your Voice</h1>
          <p>Making every vote count with transparent and secure electronic voting.</p>
          <div class="hero-buttons">
            <button class="cta-primary" onclick="login()">Start Voting</button>
  
          </div>
        </div>
      </div>
    </div>
  </section>
    <section class="features" id="features">
      <h2>Features</h2>
      <div class="features-container">
        <div class="feature-item">
          <h3>Secure Online Voting</h3>
          <p>
            Vote securely with our robust encryption and authentication
            protocols.
          </p>
        </div>
        <div class="feature-item">
          <h3>Real-time Election Results</h3>
          <p>Get accurate election results instantly after the polls close.</p>
        </div>
        <div class="feature-item">
          <h3>User-Friendly Interface</h3>
          <p>Navigate and vote with ease using our intuitive design.</p>
        </div>
        <div class="feature-item">
          <h3>Accessible to All Voters</h3>
          <p>
            Ensuring inclusivity for everyone, regardless of physical
            limitations.
          </p>
        </div>
      </div>
    </section>

    <section class="how-it-works" id="how-it-works">
      <h2>How It Works</h2>
      <div class="how-it-works-container">
        <!-- Step 1 -->
        <div class="step-container">
          <div class="step-text">
            <p class="step-number">01</p>
            <h3>Voter Registration</h3>
            <p>
              Register using a secure online platform. Your identity is verified
              with robust security measures, ensuring authenticity.
            </p>
          </div>
          <div class="step-image">
            <img
              src="assets/register.jpg"
              alt="Step 1 Illustration"
              class="step-phone-image"
            />
          </div>
        </div>

        <!-- Step 2 -->
        <div class="step-container">
          <div class="step-text">
            <p class="step-number">02</p>
            <h3>Voting</h3>
            <p>
              Cast your vote digitally, securely, and privately. The process
              ensures that your vote remains confidential and tamper-proof.
            </p>
          </div>
          <div class="step-image">
            <img
              src="assets/vote.jpg"
              alt="Step 2 Illustration"
              class="step-phone-image"
            />
          </div>
        </div>

        <!-- Step 3 -->
        <div class="step-container">
          <div class="step-text">
            <p class="step-number">03</p>
            <h3>Verification</h3>
            <p>
              Your vote is verified with multiple layers of encryption and
              authentication, ensuring no fraudulent activity.
            </p>
          </div>
          <div class="step-image">
            <img
              src="assets/verify.avif"
              alt="Step 3 Illustration"
              class="step-phone-image"
            />
          </div>
        </div>

        <!-- Step 4 -->
        <div class="step-container">
          <div class="step-text">
            <p class="step-number">04</p>
            <h3>Results</h3>
            <p>
              Election results are tabulated and shared in real-time, offering
              transparency and accuracy like never before.
            </p>
          </div>
          <div class="step-image">
            <img
              src="assets/result.png"
              alt="Step 4 Illustration"
              class="step-phone-image"
            />
          </div>
        </div>
      </div>
    </section>
    
<footer class="site-footer">
  <div class="footer-container">
    <div class="footer-left">
      <h3 class="footer-logo">BharatV</h3>
      <p class="footer-copyright">
        Copyright © 2025 by BharatV, Inc. All rights reserved.
      </p>
    </div>
    <div class="footer-right">
      <h4>Contact us</h4>
      <p>456 Bharat Marg, 2nd Floor,<br>Kerala, India 686574</p>
      <p>
        +91-8606005740<br>
        <a href="mailto:support@bharatv.com">support@bharatv.com</a>
      </p>
    </div>
  </div>
</footer>
    <script>
      function login() {
        window.location.href = "login.php";
      }

      function register() {
        window.location.href = "register_dashboard.php";
      }
    </script>

<script>
  let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    
    function showSlide(index) {
      slides.forEach(slide => slide.classList.remove('active'));
      slides[index].classList.add('active');
    }
    
    function nextSlide() {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }
    
    // Auto-advance slides every 5 seconds
    setInterval(nextSlide, 5000);
  </script>
  </body>
</html>
