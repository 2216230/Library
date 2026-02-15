<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav" style="background-color:#f9f9f9;">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>

  <div class="content-wrapper py-4">
    <div class="container">

          <!-- Image Header -->
      <div style="width:100%; text-align:center; margin-bottom:30px;">
          <img src="images/lib33.jpg" 
               alt="BSU Library" 
               style="width:100%; max-height:300px; object-fit:cover; border-radius:12px;">
      </div>
      
      <!-- Page Title -->
      <div class="text-center text-success mb-4">
        <h2 style=" font-weight:bold;">
          About BSU Library Management System
        </h2>
        <div style="width:120px; height:3px; background:#E6B800; margin:10px auto;"></div>
      </div>

      <!-- Intro -->
      <p style="font-size:15px; color:#333; line-height:1.6; text-align:justify;">
        The <span style="color:#004d00; font-weight:bold;">BSU Library Management System</span> is designed to make 
        library operations faster, easier, and more efficient. It allows students to 
        <span style="color:#E6B800; font-weight:bold;">search</span>, 
        <span style="color:#E6B800; font-weight:bold;">borrow</span>, 
        and <span style="color:#E6B800; font-weight:bold;">track books</span> while helping the library staff 
        manage resources effectively.
      </p>

      <p style="font-size:15px; color:#333; line-height:1.6; text-align:justify;">
        Our mission is to support students and faculty by providing quick access to 
        academic resources through a user-friendly and reliable system. 
      </p>

      <!-- Vision, Mission, Goals & Objectives -->
      <div class="mt-5">
        <h2 style="color:#004d00; font-weight:bold;">Vision, Mission, Goals & Objectives</h2>
        <p style="color:#777; font-size:14px; font-style:italic;">
          (Board Resolution No. 183, series of 2025)
        </p>

        <!-- Vision -->
        <div class="mt-3 p-4 rounded" style="background:#004d00; color:#fff;">
          <h3 style="color:#E6B700;">Vision</h3>
          <p class="mb-0">A premier university in transformative education, innovative research, inclusive extension services, sustainable development, and stewardship of culture and the environment.</p>
        </div>

        <!-- Mission -->
        <div class="mt-3 p-4 rounded" style="border:2px solid #004d00;">
          <h3 style="color:#004d00;">Mission</h3>
          <p class="mb-0">Cultivate resilient and future-ready human capital through excellent teaching, responsive research, proactive and sustainable community engagements, strategic partnerships, and progressive leadership.</p>
        </div>

        <!-- Goals & Objectives -->
        <div class="mt-4">
          <h3 style="color:#004d00;">Goals and Objectives</h3>

          <?php 
          $goals = [
            [
              "title" => "GOAL 1: (Instruction) Ensure equity in accessing quality higher education",
              "items" => [
                "Formulate and implement affirmative action policies aligned with Free Higher Education",
                "Sustain enrollment of disadvantaged students",
                "Sustain grants and scholarships for undergraduate and graduate students",
                "Develop learning continuity and student affairs services plan",
                "Implement inclusive education strategies for diverse learners",
                "Sustain optimal enrollment in all degree programs"
              ]
            ],
            [
              "title" => "GOAL 2: (Instruction) Advance quality and relevant instruction to boost regional economies",
              "items" => [
                "Enhance instruction through a supportive and innovative environment",
                "Continuously improve tertiary and advanced education standards"
              ]
            ],
            [
              "title" => "GOAL 3: (Research) Develop pioneering science and gender and culture-sensitive solutions",
              "items" => [
                "Strengthen policies, culture, linkages and support for research and development",
                "Advance scholarly capabilities of faculty and staff through research-driven initiatives and activities",
                "Conduct socially-responsive and impactful research"
              ]
            ],
            [
              "title" => "GOAL 4: (Extension) Develop proactive extension programs for disadvantaged communities and vulnerable sectors",
              "items" => [
                "Enhance extension system environment for effective community development",
                "Build capacity among extension service providers",
                "Implement culturally-relevant and gender-sensitive engagement programs",
                "Expand reach and inclusivity of extension initiatives",
                "Evaluate and amplify the impact of extension programs and projects"
              ]
            ],
            [
              "title" => "GOAL 5: (Administration and Finance) Promote integrity-based governance and efficient management of resources",
              "items" => [
                "Develop human resource capabilities",
                "Cultivate a culture of good governance and resource stewardship",
                "Develop smart and green campus solutions",
                "Establish sound financial policies and systems"
              ]
            ],
            [
              "title" => "GOAL 6: (Business Affairs) Balance progressive resource development while maintaining existing resources",
              "items" => [
                "Foster a supportive ecosystem for entrepreneurial ventures",
                "Create strategic and socially-responsible business partnerships",
                "Strengthen capabilities in revenue-generating activities",
                "Optimize existing income sources",
                "Pursue green enterprises and growth opportunities"
              ]
            ],
            [
              "title" => "GOAL 7: Strengthen and expand strategic partnerships",
              "items" => [
                "Build a dynamic and enabling environment for partnerships",
                "Deepen engagement with alumni, government, and civil society",
                "Strengthen multi-sectoral collaboration among academe, LGUs, industries, and communities"
              ]
            ]
          ];

          foreach ($goals as $goal) {
            echo "<div class='mt-4'>
                    <h4 style='color:#E6B800; font-weight:bold;'>{$goal['title']}</h4>
                    <ul style='margin-left:20px;'>";
            foreach ($goal['items'] as $item) {
              echo "<li>{$item}</li>";
            }
            echo "</ul></div>";
          }
          ?>
        </div>
      </div>

    </div>
  </div>

</div>

<?php include 'includes/scripts.php'; ?>

<style>
  @media (max-width: 768px) {
    h2, h3, h4 { font-size: 18px !important; }
    p, li { font-size: 14px !important; }
    .content-wrapper { padding: 10px; }
  }

  /* Make yellow headers more visible on all backgrounds */
  h3[style*="#FFD700"], h4[style*="#FFD700"], span[style*="#FFD700"] {
    color: #E6B800 !important;
  }
</style>

<!-- ================= FOOTER ================= -->
<?php include 'includes/footer.php'; ?>
</body>
</html>
