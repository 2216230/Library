<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; 
include 'includes/scripts.php'; ?>


<body class="hold-transition skin-blue layout-top-nav" style="background-color:#f9f9f9;">
<?php include 'includes/navbar.php'; ?>
<div class="wrapper py-3">



<!-- =================== MAIN CONTENT =================== -->
  <main class="container py-3">

    <!-- Page Header -->
    <div class="text-center mb-4">
      <h2 class="fw-bold text-success">
        <i class="fa fa-comment-dots me-2 text-warning"></i>
        Client Satisfaction Feedback
      </h2>
      <p class="text-muted mb-0">
        We value your feedback! Please fill out this short survey to help us improve our library services.
      </p>
    </div>

    <!-- Google Form Embed -->
    <div style="margin-top:20px; border:2px solid #004d00; border-radius:12px; overflow:hidden;">
        <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSeUd_hi2zA2TrMkugT1XrYMJ1UShIrHueWjf7SXf2BqXgm7zw/viewform?embedded=true" 
                width="100%" 
                height="600" 
                frameborder="0" 
                marginheight="0" 
                marginwidth="0" 
                style="border:none;">
          Loading…
        </iframe>
      </div>

  </main>

</div>

<!-- ================= FOOTER ================= -->
<?php include 'includes/footer.php'; ?>
</body>
</html>
