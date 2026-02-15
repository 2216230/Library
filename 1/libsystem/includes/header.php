<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BSU Library </title>

  <link rel="icon" type="image/jpeg" href="../images/logo.jpg">
  <link rel="icon" type="image/png" href="../images/logo.png">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  
  <style>
    :root{
      --primary: #20650A;         /* BSU Official Green */
      --primary-dark: #184d08;    /* Darker BSU Green */
      --primary-light: #e8f5e8;
      --accent: #F0D411;          /* BSU Official Yellow */
      --danger: #FF8C00;          /* warm orange for CTAs/alerts */
      --danger-dark: #D35400;     /* deeper burnt orange for gradients */
      --warning: #F0D411;         /* BSU Official Yellow */
      --success: #28a745;
      --info: #1E90FF;
      --secondary: #8A2BE2;
    }

    /* Bootstrap 3 compatibility overrides (ensure admin theme follows palette) */
    .btn-primary { background-color: var(--primary) !important; border-color: var(--primary-dark) !important; color: #fff !important; }
    .btn-primary:hover, .btn-primary:focus { background-color: var(--primary-dark) !important; border-color: var(--primary-dark) !important; }
    .btn-success { background-color: var(--success) !important; border-color: darken(var(--success, #28a745), 10%) !important; color: #fff !important; }
    .btn-danger, .btn-danger:focus { background-color: var(--danger) !important; border-color: var(--danger-dark) !important; color: #fff !important; }
    .btn-warning { background-color: var(--accent) !important; border-color: var(--warning) !important; color: var(--danger) !important; }
    .label-primary, .bg-primary { background-color: var(--primary) !important; color: #fff !important; }
    .text-primary { color: var(--primary) !important; }
    .bg-danger { background-color: var(--danger) !important; color: #fff !important; }

    /* Fallbacks for older AdminLTE rules */
    .box-header, .box-header.with-border { background: var(--primary-dark) !important; color: #fff !important; border-bottom-color: rgba(0,0,0,0.06) !important; }

    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
    }

    #bg {
      background: #006400;
    }

    .header-logo {
      height: 60px;
      width: auto;
    }

    /* Prevent footer gap on pages */
    .wrapper { min-height: auto !important; height: auto !important; }
    .content-wrapper { min-height: auto !important; }
    .content { padding: 15px !important; }

    .mobile-header {
      display: none;
      padding: 0.5rem 1rem;
    }

    .desktop-header {
      display: block;
    }

    .time-display {
      font-size: 0.85rem;
      white-space: nowrap;
    }

    .mobile-university-name {
      font-size: 0.9rem;
      font-weight: bold;
      color: #FFD700;
      text-align: center;
      margin: 0 0.5rem;
      line-height: 1.2;
    }

    /* Mobile styles */
    @media (max-width: 992px) {
      .desktop-header {
        display: none;
      }
      
      .mobile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      
      .header-logo {
        height: 50px;
      }
      
      .time-display {
        font-size: 0.75rem;
      }
      
      .mobile-university-name {
        font-size: 0.8rem;
      }
    }

    /* Small mobile devices */
    @media (max-width: 576px) {
      .header-logo {
        height: 45px;
      }
      
      .time-display {
        font-size: 0.7rem;
      }
      
      .mobile-university-name {
        font-size: 0.75rem;
      }
    }

    /* Extra small devices */
    @media (max-width: 400px) {
      .mobile-university-name {
        font-size: 0.7rem;
      }
      
      .time-display {
        font-size: 0.65rem;
      }
    }
  </style>
</head>
<body>

<!-- ================= MOBILE HEADER (Visible on small screens) ================= -->
<header id="bg" class="border-bottom border-warning mobile-header">
  <div class="d-flex justify-content-between align-items-center w-100">
    <!-- Left: Logo -->
    <div class="d-flex align-items-center">
      <a href="index.php">
        <img src="images/bokod.png" alt="BSU Logo" class="header-logo">
      </a>
    </div>
    
    <!-- Center: University Name -->
    <div class="mobile-university-name flex-grow-1 text-center px-2">
      BENGUET STATE UNIVERSITY - BOKOD CAMPUS
    </div>
    
    <!-- Right: Time -->
    <div class="text-warning fw-bold time-display text-end">
      <div>PH Time:</div>
      <div id="mobileDateTime"></div>
    </div>
  </div>
</header>

<!-- ================= DESKTOP HEADER (Visible on larger screens) ================= -->
<header id="bg" class="border-bottom border-warning py-1 desktop-header">
  <div class="container">
    <div class="row align-items-center text-center text-lg-start">
      
      <!-- Left: Logos -->
      <div class="col-12 col-lg-3 mb-2 mb-lg-0 d-flex justify-content-center justify-content-lg-start align-items-center">
        <!--<img src="images/bagongphil.png" alt="Philippines Flag" class="me-2 img-fluid" style="height:80px;">-->
        <a href="index.php">
          <img src="images/bokod.png" alt="BSU Logo" class="img-fluid header-logo" style="height:80px;">
        </a>
      </div>

      <!-- Center: University Info -->
      <div class="col-12 col-lg-6 text-center">
        <div class="fw-bold small text-warning">REPUBLIC OF THE PHILIPPINES</div>
        <div class="fw-bold text-warning fs-6">BENGUET STATE UNIVERSITY - BOKOD CAMPUS</div>
        <div class="small text-warning">AMBANGEG, DAKLAN, BOKOD, BENGUET, 2605 PHILIPPINES</div>
      </div>

      <!-- Right: Date/Time -->
      <div class="col-12 col-lg-3 text-lg-end text-center mt-2 mt-lg-0 small text-warning fw-bold">
        Philippine Standard Time:<br>
        <span id="currentDateTime"></span>
      </div>

    </div>
  </div>
</header>



<script>
function updateDateTime() {
  const now = new Date();
  const dateTimeString = now.toLocaleString("en-PH", {
      timeZone: "Asia/Manila",
      hour12: true,
      year: "numeric", 
      month: "short", 
      day: "numeric",
      hour: "2-digit", 
      minute: "2-digit"
  });
  
  // Update both desktop and mobile time displays
  document.getElementById("currentDateTime").innerText = dateTimeString;
  document.getElementById("mobileDateTime").innerText = dateTimeString;
}

// Update time immediately and then every 30 seconds
updateDateTime();
setInterval(updateDateTime, 30000);
</script>

</body>
</html>