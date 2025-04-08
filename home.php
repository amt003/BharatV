<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BharatV - Voting Made Simple</title>
    <style>
      /* General Reset */
body,
h1,
h2,
h3,
p,
ul,
ol,
li {
  margin: 0;
  padding: 0;
  list-style: none;
}
body {
  font-family: "Poppins", sans-serif;
  color: #333;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
  line-height: 1.6;
  overflow-x: hidden;
}

/* Navbar */
.navbar {
  display: flex;
  background-color: white;
  justify-content: space-between;
  align-items: center;
  padding: 17px 40px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}
.navbar h1 {
  font-size: 2em;
  color: #ff9933;
}
.navbar span {
  color: #555;
  font-size: 0.9em;
  font-weight: 400;
}
.navbar nav ul {
  display: flex;
  gap: 20px;
}
.navbar nav ul li a {
  text-decoration: none;
  color: #333;
  font-weight: bold;
  font-size: 19px;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background-color 0.3s, color 0.3s;
}
.navbar nav ul li a:hover {
  background: #ffd699;
  color: white;
}
.auth-buttons button {
  font-size: 1em;
  padding: 10px 20px;
  background: #4caf50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}
.auth-buttons button:hover {
  background: #45a049;
  transform: scale(1.1);
}
.company-logo {
  top: 2px;
  left: 10px;
  display: flex;
  align-items: center;
}

.company-logo img {
  width: 140px;
  height: auto;
}

/* Hero Section */
.hero {
  height: 100vh;
  position: relative;
  overflow: hidden;
}

.slider {
  height: 100%;
  position: relative;
}

.slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 0.5s ease-in-out;
  background: linear-gradient(45deg, green);
}

.slide::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgb(93, 160, 93);
}

.slide.active {
  opacity: 1;
}

.hero-content {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: white;
  width: 80%;
  max-width: 800px;
}

.hero-content h1 {
  font-size: 3.5rem;
  margin-bottom: 1.5rem;
}

.hero-content p {
  font-size: 1.2rem;
  margin-bottom: 2rem;
}

.hero-buttons button {
  padding: 1rem 2rem;
  margin: 0 0.5rem;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 1.1rem;
  transition: transform 0.3s;
}

.hero-buttons button:hover {
  transform: translateY(-2px);
}

.cta-primary {
  background: #ff9933;
  color: green;
}

/* Features Section */
.features {
  padding: 40px 20px;
  text-align: center;
  background-color: #fff;
}
.features h2 {
  text-align: center;
  color: #ff9933;
  font-size: 2.5em;
  margin-bottom: 40px;
  position: relative;
}

.features h2::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 50px;
  height: 3px;
  background-color: #ff9933; 
}
.features-container {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
  margin-top: 20px;
}
.feature-item {
  background: #f4f4f4;
  padding: 20px;
  width: 200px;
  height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(126, 170, 56, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}
.feature-item:hover {
  transform: translateY(-10px);
  box-shadow: 0 4px 10px green;
}
.feature-item h3 {
  margin-bottom: 10px;
  font-size: 1.2em;
  color: #333;
}
.feature-item p {
  font-size: 0.9em;
  color: #666;
}

/* How It Works Section */
.how-it-works {
  background-color: #fff;
  padding: 60px 20px;
  text-align: left;
}

.how-it-works h2 {
  text-align: center;
  color: #ff9933;
  font-size: 2.5em;
  margin-bottom: 40px;
  position: relative;
}

.how-it-works h2::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 50px;
  height: 3px;
  background-color: #ff9933; 
}

.how-it-works-container {
  display: flex;
  flex-direction: column;
  gap: 60px;
  max-width: 1200px;
  margin: 0 auto;
}

.step-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 30px;
  width: 100%;
}

.step-container:nth-child(even) {
  flex-direction: row-reverse;
}

.step-text {
  max-width: 50%;
}

.step-number {
  font-size: 5em;
  font-weight: bold;
  color: #f0f0f0;
  margin: 0;
  margin-bottom: -10px;
  position: relative;
  z-index: -1;
}

.step-text h3 {
  font-size: 1.8em;
  font-weight: bold;
  color: #333;
  margin: 20px 0 10px;
}

.step-text p {
  font-size: 1em;
  line-height: 1.6;
  color: #555;
}

.step-image {
  max-width: 300px;
  text-align: center;
}

.step-phone-image {
  max-width: 100%;
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
.site-footer {
  background: linear-gradient(135deg, rgb(21, 92, 21), rgb(59, 151, 59));
  color: #ffffff;
  padding: 2rem 2rem;
  font-family: "Inter", sans-serif;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
}

.footer-left {
  animation: fadeIn 1s ease-in;
}

.footer-logo {
  font-size: 2.5rem;
  margin: 0;
  background: linear-gradient(45deg, darkorange, orange);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 700;
}

.footer-copyright {
  color: white;
  font-size: 0.9rem;
  margin-top: 1rem;
}

.footer-right {
  animation: slideIn 1s ease-out;
}

.footer-right h4 {
  font-size: 1.2rem;
  margin-bottom: 1.5rem;
  position: relative;
}

.footer-right h4::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -8px;
  width: 40px;
  height: 3px;
  background: orange;
}

.footer-right p {
  margin: 0.5rem 0;
  line-height: 1.6;
}

.footer-right a {
  color: white;
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-right a:hover {
  color: orange;
}

/* Custom scrollbar styles */
::-webkit-scrollbar {
  width: 10px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #4caf50;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #45a049;
}

/* Hide the scrollbar arrows */
::-webkit-scrollbar-button {
  display: none;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    transform: translateX(20px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 508px) {
  .footer-container {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

    </style>

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
            <li><a href="#contact-us">Contact Us</a></li>

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
      <h4 id="contact-us">Contact us</h4>
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
