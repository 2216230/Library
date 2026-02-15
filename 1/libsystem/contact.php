<?php 
include 'includes/session.php';
include 'includes/header.php';
include 'includes/mailer.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    if (sendContactMail($name, $email, $message)) {
        $success = "✅ Message sent successfully!";
    } else {
        $error = "❌ Failed to send message. Please check mail configuration.";
    }
}

?>

<body class="bg-light">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>

    <div class="content-wrapper py-3">
    <!-- =================== MAIN CONTENT =================== -->


    </div>
    <div class="container">

      <!-- Page Title -->
      <div class="text-center mb-4">
        <h2 class="fw-bold text-success">Contact Us</h2>
        <div class="mx-auto" style="width:100px; height:3px; background:#FFD700;"></div>
      </div>

      <!-- Alerts -->
      <?php if(isset($success)): ?>
        <div class="alert alert-success text-center fw-bold"><?= $success ?></div>
      <?php elseif(isset($error)): ?>
        <div class="alert alert-danger text-center fw-bold"><?= $error ?></div>
      <?php endif; ?>

      <!-- Two Column Layout -->
      <div class="row align-items-start justify-content-center g-5">

        <!-- Left Column: Contact Info -->
        <div class="col-lg-5 col-md-6 text-center text-md-start">
          <h4 class="fw-bold text-success mb-3">Get in Touch</h4>
          <p class="text-secondary mb-4">
            If you have questions, concerns, or suggestions, feel free to reach us:
          </p>

          <ul class="list-unstyled fs-6 text-success">
            <li class="mb-3">
              <i class="fa fa-envelope text-warning me-2"></i>
              <b>Email:</b> zhanonarevalo@gmail.com
            </li>
            <li class="mb-3">
              <i class="fa fa-phone text-warning me-2"></i>
              <b>Phone:</b> +63 912 345 6789
            </li>
            <li class="mb-3">
              <i class="fa fa-map-marker-alt text-warning me-2"></i>
              <b>Address:</b> Benguet State University, Bokod Campus
            </li>
          </ul>

          <div class="mt-4">
            <h6 class="text-success fw-bold mb-2">Office Hours:</h6>
            <p class="text-secondary mb-1">Monday – Friday: 8:00 AM – 5:00 PM</p>
            <p class="text-secondary">Closed on weekends and holidays</p>
          </div>
        </div>

        <!-- Right Column: Contact Form -->
        <div class="col-lg-6 col-md-6">
          <h4 class="fw-bold text-success mb-3 text-center text-md-start">Send Us a Message</h4>

          <form method="post" action="" class="p-4 rounded-3 border border-success bg-white shadow-sm">
            <div class="mb-3">
              <label for="name" class="form-label fw-bold text-success">Your Name</label>
              <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your name">
            </div>

            <div class="mb-3">
              <label for="email" class="form-label fw-bold text-success">Your Email</label>
              <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email">
            </div>

            <div class="mb-3">
              <label for="message" class="form-label fw-bold text-success">Message</label>
              <textarea id="message" name="message" rows="5" class="form-control" required placeholder="Write your message..."></textarea>
            </div>

            <div class="text-center text-md-end">
              <button type="submit" class="btn btn-success px-4 fw-bold">
                <i class="fa fa-paper-plane me-1"></i> Send Message
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- ================= FOOTER ================= -->
<?php include 'includes/footer.php'; ?>

  <?php include 'includes/scripts.php'; ?>
</body>
</html>
