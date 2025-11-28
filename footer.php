<?php
// footer.php
?>
</main>

<style>
  .site-footer {
    background: linear-gradient(135deg, #0a4a4e 0%, #0d5a5f 50%, #0a4a4e 100%);
    color: #fff;
    padding: 60px 0 30px 0;
    position: relative;
    overflow: hidden;
  }
  
  .site-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #00a9b4, #008c97, #00a9b4);
    background-size: 200% 100%;
    animation: gradientShift 3s ease-in-out infinite;
  }
  
  @keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
  }
  
  .footer-brand {
    position: relative;
  }
  
  .footer-brand h4 {
    background: linear-gradient(45deg, #00a9b4, #ffffff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.8rem;
    margin-bottom: 15px;
    font-weight: 700;
  }
  
  .footer-section h5 {
    color: #00a9b4;
    font-weight: 600;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
  }
  
  .footer-section h5::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 2px;
    background: linear-gradient(90deg, #00a9b4, #008c97);
    border-radius: 2px;
  }
  
  .footer-links li {
    margin-bottom: 8px;
  }
  
  .footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
    padding: 2px 0;
  }
  
  .footer-links a:hover {
    color: #00a9b4;
    transform: translateX(5px);
    text-decoration: none;
  }
  
  .contact-info p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 10px;
    transition: all 0.3s ease;
  }
  
  .contact-info p:hover {
    color: #00a9b4;
    transform: translateX(3px);
  }
  
  .contact-info i {
    color: #00a9b4;
    width: 20px;
    text-align: center;
  }
  
  .social-icons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .social-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00a9b4, #008c97);
    color: white !important;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 169, 180, 0.3);
  }
  
  .social-btn:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 25px rgba(0, 169, 180, 0.4);
    color: white !important;
    background: linear-gradient(135deg, #008c97, #00a9b4);
  }
  
  .footer-divider {
    background: linear-gradient(90deg, transparent, rgba(0, 169, 180, 0.5), transparent);
    height: 1px;
    border: none;
    margin: 40px 0 30px 0;
  }
  
  .footer-bottom {
    text-align: center;
    padding-top: 20px;
    color: rgba(255, 255, 255, 0.7);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
  }
  
  .footer-bottom small {
    font-size: 0.9rem;
  }
</style>

<footer class="site-footer mt-5">
  <div class="container">
    <div class="row">
      <!-- Logo & Description -->
      <div class="col-md-4 mb-4">
        <div class="footer-brand">
          <h4>DeptConnect</h4>
          <p style="color: rgba(255, 255, 255, 0.9); line-height: 1.6;">
            <i class="fas fa-graduation-cap me-2" style="color: #00a9b4;"></i>
            Connecting students, faculty, and administration through our comprehensive academic portal. 
            <strong style="color: #00a9b4;">Empowering education with technology.</strong>
          </p>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-md-2 mb-4">
        <div class="footer-section">
          <h5><i class="fas fa-link me-2"></i>Quick Links</h5>
          <ul class="list-unstyled footer-links">
            <li><a href="index.php"><i class="fas fa-home me-2"></i>Home</a></li>
            <li><a href="about.php"><i class="fas fa-info-circle me-2"></i>About Us</a></li>
            <li><a href="faculty.php"><i class="fas fa-chalkboard-teacher me-2"></i>Faculty</a></li>
            <li><a href="courses.php"><i class="fas fa-book me-2"></i>Courses</a></li>
            <li><a href="student_login.php"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
          </ul>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-md-3 mb-4">
        <div class="footer-section">
          <h5><i class="fas fa-address-book me-2"></i>Contact Us</h5>
          <div class="contact-info">
            <p><i class="fas fa-envelope me-2"></i>dept@example.edu</p>
            <p><i class="fas fa-phone me-2"></i>+91 12345 67890</p>
            <p><i class="fas fa-map-marker-alt me-2"></i>Department of Computer Engineering</p>
            <p><i class="fas fa-clock me-2"></i>Mon - Fri: 9:00 AM - 5:00 PM</p>
          </div>
        </div>
      </div>

      <!-- Follow Us -->
      <div class="col-md-3 mb-4">
        <div class="footer-section">
          <h5><i class="fas fa-share-alt me-2"></i>Follow Us</h5>
          <div class="social-icons">
            <a href="#" class="social-btn" title="Facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-btn" title="Twitter">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="social-btn" title="LinkedIn">
              <i class="fab fa-linkedin-in"></i>
            </a>
            <a href="#" class="social-btn" title="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
          </div>
          <p class="text-center mt-3" style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">
            Stay connected with us
          </p>
        </div>
      </div>
    </div>

    <hr class="footer-divider">

    <div class="footer-bottom">
      <small>
        <i class="fas fa-copyright me-1"></i>
        <?= date('Y') ?> DeptConnect. All rights reserved. | 
        <span style="color: #00a9b4;">Made with <i class="fas fa-heart" style="color: #ff6b6b;"></i> for Education</span>
      </small>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });
</script>
</body>
</html>
