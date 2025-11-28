<?php include 'header.php';
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Courses Offered | Academic Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #35b36f;
      --text-dark: #333;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fff;
      color: var(--text-dark);
    }
    header {
      background: linear-gradient(rgba(53,179,111,0.75), rgba(42,156,98,0.75)), url('assets/classroom.jpg') center/cover no-repeat;
      color: #fff;
      text-align: center;
      padding: 100px 20px;
    }
    header h1 {
      font-weight: 700;
      font-size: 3rem;
    }
    .course-section {
      padding: 60px 0;
    }
    .section-title {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 40px;
      text-align: center;
    }
    .card {
      border: none;
      background: #f9f9f9;
      transition: 0.3s ease;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    .card-header {
      background-color: var(--primary-color);
      color: white;
      font-weight: 600;
      font-size: 1.2rem;
    }
    .btn-outline-primary {
      color: var(--primary-color);
      border-color: var(--primary-color);
    }
    .btn-outline-primary:hover {
      background-color: var(--primary-color);
      color: white;
    }
  </style>
</head>
<body>

<!-- Header -->
<header data-aos="fade-down">
  <h1>Courses Offered</h1>
  <p>Explore our Undergraduate, Postgraduate, and Ph.D. Programs</p>
</header>

<!-- Undergraduate Section -->
<section class="course-section" id="ug">
  <div class="container">
    <h2 class="section-title" data-aos="fade-up">Undergraduate Programs (UG)</h2>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4" data-aos="zoom-in">
        <div class="card h-100">
          <div class="card-header"><i class="fa-solid fa-laptop-code me-2"></i>B.Tech - Computer Engineering</div>
          <div class="card-body">
            <p>The B.Tech in Computer Engineering program provides a solid foundation in software development, computer systems, and emerging technologies.</p>
            <ul>
              <li>Duration: 4 Years</li>
              <li>Eligibility: 12th Science (PCM)</li>
              <li>Intake: 120 Students</li>
            </ul>
            <a href="#" class="btn btn-outline-primary btn-sm mt-2"><i class="fa-solid fa-file-pdf me-1"></i> View Syllabus</a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-4" data-aos="zoom-in">
        <div class="card h-100">
          <div class="card-header"><i class="fa-solid fa-network-wired me-2"></i>B.Tech - Information Technology</div>
          <div class="card-body">
            <p>Focuses on information systems, networking, cybersecurity, and IT solutions relevant to modern industries.</p>
            <ul>
              <li>Duration: 4 Years</li>
              <li>Eligibility: 12th Science (PCM)</li>
              <li>Intake: 60 Students</li>
            </ul>
            <a href="#" class="btn btn-outline-primary btn-sm mt-2"><i class="fa-solid fa-file-pdf me-1"></i> View Syllabus</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Postgraduate Section -->
<section class="course-section bg-light" id="pg">
  <div class="container">
    <h2 class="section-title" data-aos="fade-up">Postgraduate Programs (PG)</h2>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4" data-aos="zoom-in">
        <div class="card h-100">
          <div class="card-header"><i class="fa-solid fa-database me-2"></i>M.Tech - Computer Engineering</div>
          <div class="card-body">
            <p>Advanced program focused on research, AI, data analytics, and deep learning methodologies.</p>
            <ul>
              <li>Duration: 2 Years</li>
              <li>Eligibility: B.E./B.Tech in Computer/IT</li>
            </ul>
            <a href="#" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> View Syllabus</a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-4" data-aos="zoom-in">
        <div class="card h-100">
          <div class="card-header"><i class="fa-solid fa-cloud me-2"></i>M.Tech - Data Science</div>
          <div class="card-body">
            <p>Specialized program in big data, machine learning, and cloud-based analytics with industry-oriented research.</p>
            <ul>
              <li>Duration: 2 Years</li>
              <li>Eligibility: B.E./B.Tech (Any Stream)</li>
            </ul>
            <a href="#" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> View Syllabus</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Ph.D. Section -->
<section class="course-section" id="phd">
  <div class="container">
    <h2 class="section-title" data-aos="fade-up">Doctoral Program (Ph.D.)</h2>
    <div class="row g-4 justify-content-center">
      <div class="col-md-8" data-aos="zoom-in">
        <div class="card">
          <div class="card-header"><i class="fa-solid fa-microscope me-2"></i>Ph.D. - Computer Engineering</div>
          <div class="card-body">
            <p>The doctoral program emphasizes independent research in Artificial Intelligence, IoT, Software Engineering, and Data Science. Scholars are guided by experienced faculty with global research exposure.</p>
            <ul>
              <li>Duration: Minimum 3 Years</li>
              <li>Eligibility: M.Tech / M.E. in relevant discipline</li>
              <li>Mode: Full-time / Part-time</li>
            </ul>
            <a href="#" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> View Detailed Structure</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<?php 
include 'footer.php' ; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });
</script>
</body>
</html>
