<?php
include 'header.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Department | Academic Portal</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Icons & Fonts -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <!-- AOS Animation -->
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

<style>
  body {
      font-family: 'Poppins', sans-serif;
      background-color: #fff;
      color: #333;
    }
  .about-hero {
    background: linear-gradient(rgba(53,179,111,0.75), rgba(42,156,98,0.75)), url('assets/about-bg.jpg') center/cover no-repeat;
    color: white;
    text-align: center;
    padding: 100px 20px;
    border-radius: 0;
  }
  .section-title {
    color: #35b36f;
    font-weight: 700;
    margin-bottom: 20px;
  }
  .vision-mission {
    background-color: #f8f9fa;
    padding: 50px 20px;
    border-radius: 15px;
  }
  .vision-mission h4 {
    color: #2a9c62;
    font-weight: 600;
  }
  .infra-gallery img {
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s;
  }
  .infra-gallery img:hover {
    transform: scale(1.05);
  }
</style>

<!-- Page Hero -->
<section class="about-hero" data-aos="fade-up">
  <h1>About the Department</h1>
  <p class="lead">Department of Computer Engineering | Academic Excellence & Innovation</p>
  
</section>

<!-- History / Overview -->
<section class="py-5">
  <div class="container" data-aos="fade-up">
    <h2 class="section-title text-center">Department Overview</h2>
    <p class="text-center col-lg-10 mx-auto">
      The Department of Computer Engineering was established with a vision to nurture young minds into
      competent professionals in the field of computing and technology. The department offers a wide range
      of academic programs from undergraduate to doctoral levels, with emphasis on hands-on learning, 
      research, and innovation. Our faculty members are dedicated to providing an inspiring learning 
      environment supported by state-of-the-art laboratories and facilities.
    </p>
  </div>
</section>

<!-- Vision & Mission -->
<section class="vision-mission my-5" data-aos="fade-right">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-md-6">
        <h4><i class="fa-solid fa-eye me-2"></i>Vision</h4>
        <p>To be a center of excellence in computer engineering education and research, producing globally competent and ethically responsible technocrats.</p>
      </div>
      <div class="col-md-6">
        <h4><i class="fa-solid fa-bullseye me-2"></i>Mission</h4>
        <ul>
          <li>Provide high-quality education in computer engineering with emphasis on innovation and critical thinking.</li>
          <li>Promote research and industry collaboration for real-world impact.</li>
          <li>Develop professionals equipped with leadership, teamwork, and lifelong learning skills.</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Infrastructure -->
<section class="py-5" data-aos="zoom-in">
  <div class="container">
    <h2 class="section-title text-center">Infrastructure & Facilities</h2>
    <div class="row g-4 infra-gallery">
      <div class="col-md-4"><img src="assets/lab1.jpeg" class="img-fluid" alt="Lab 1"></div>
      <div class="col-md-4"><img src="assets/lab2.jpeg" class="img-fluid" alt="Lab 2"></div>
      <div class="col-md-4"><img src="assets/classroom.jpeg" class="img-fluid" alt="Classroom"></div>
    </div>
  </div>
</section>

<!-- Achievements -->
<section class="py-5 bg-light" data-aos="fade-up">
  <div class="container">
    <h2 class="section-title text-center">Department Achievements</h2>
    <div class="row text-center g-4">
      <div class="col-md-3">
        <i class="fa-solid fa-trophy fa-2x mb-3" style="color:#2a9c62;"></i>
        <h5>50+ Awards</h5>
        <p>National & international recognitions for research and innovation.</p>
      </div>
      <div class="col-md-3">
        <i class="fa-solid fa-flask fa-2x mb-3" style="color:#2a9c62;"></i>
        <h5>10+ Research Labs</h5>
        <p>Dedicated facilities for AI, IoT, Data Science, and Robotics.</p>
      </div>
      <div class="col-md-3">
        <i class="fa-solid fa-user-graduate fa-2x mb-3" style="color:#2a9c62;"></i>
        <h5>1000+ Graduates</h5>
        <p>Successful alumni working at top global organizations.</p>
      </div>
      <div class="col-md-3">
        <i class="fa-solid fa-handshake fa-2x mb-3" style="color:#2a9c62;"></i>
        <h5>30+ MoUs</h5>
        <p>Active collaborations with industry and research institutions.</p>
      </div>
    </div>
  </div>
</section>



<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });
</script>

</body>
</html>
<?php include 'footer.php'; ?>