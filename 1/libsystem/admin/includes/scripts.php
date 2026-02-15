<?php 
// Determine the correct path based on where this file is being called from
if (!isset($base_path)) {
  $base_path = (strpos($_SERVER['PHP_SELF'], '/superadmin/') !== false) ? '../../' : '../';
}
?>
<!-- jQuery 3 -->
<script src="<?php echo $base_path; ?>bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?php echo $base_path; ?>bower_components/jquery-ui/jquery-ui.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="<?php echo $base_path; ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Moment JS - COMMENTED OUT (not installed) -->
<!-- <script src="<?php echo $base_path; ?>bower_components/moment/moment.js"></script> -->
<!-- DataTables -->
<script src="<?php echo $base_path; ?>bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo $base_path; ?>bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!-- ChartJS -->
<script src="<?php echo $base_path; ?>bower_components/chart.js/Chart.js"></script>
<!-- daterangepicker -->
<!-- <script src="<?php echo $base_path; ?>bower_components/moment/min/moment.min.js"></script> -->
<script src="<?php echo $base_path; ?>bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="<?php echo $base_path; ?>bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- bootstrap time picker -->
<script src="<?php echo $base_path; ?>plugins/timepicker/bootstrap-timepicker.min.js"></script>
<!-- Slimscroll -->
<script src="<?php echo $base_path; ?>bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?php echo $base_path; ?>bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo $base_path; ?>dist/js/adminlte.min.js"></script>

<!-- Active Script -->
<script>
$(function(){
	/** add active class and stay opened when selected */
	var currentPage = window.location.pathname.split('/').pop(); // Get just the filename
	
	// for sidebar menu entirely but not cover treeview
	$('ul.sidebar-menu > li > a').each(function() {
	    var href = $(this).attr('href');
	    // Only match exact filename, not partial matches
	    if(href && href === currentPage) {
	        $(this).parent().addClass('active');
	    }
	});

	// for treeview
	$('ul.treeview-menu a').each(function() {
	    var href = $(this).attr('href');
	    // Only match exact filename
	    if(href && href === currentPage) {
	        $(this).parentsUntil(".sidebar-menu > .treeview-menu").addClass('active');
	        $(this).closest('li').addClass('active');
	    }
	});

	// Custom Treeview toggle functionality (override AdminLTE default)
	$('.sidebar-menu .treeview > a').off('click').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		var $parent = $(this).parent();
		$parent.toggleClass('menu-open');
		$parent.find('> .treeview-menu').slideToggle(200);
	});

	// Auto-open treeview if a child is active
	$('.sidebar-menu .treeview').each(function() {
		if ($(this).find('.treeview-menu li.active').length > 0) {
			$(this).addClass('menu-open');
			$(this).find('> .treeview-menu').show();
		}
	});

});
</script>
<!-- Data Table Initialize -->


<!-- For the navbar-->
<script>
	
// Add responsive behavior
$(document).ready(function() {
  // Handle window resize
  $(window).resize(function() {
    adjustHeaderForMobile();
  });
  
  function adjustHeaderForMobile() {
    if ($(window).width() < 992) {
      $('.logo-lg .logo-text small').hide();
    } else {
      $('.logo-lg .logo-text small').show();
    }
  }
  
  // Initial adjustment
  adjustHeaderForMobile();
  
  // Smooth hover effects
  $('.dropdown').hover(function() {
    $(this).addClass('open');
  }, function() {
    $(this).removeClass('open');
  });

  // Mobile Sidebar Toggle
  var $body = $('body');
  
  // Toggle sidebar on mobile menu button click
  $('.sidebar-toggle').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('Sidebar toggle clicked');
    console.log('Window width:', $(window).width());
    console.log('Current classes:', $body.attr('class'));
    
    // Check if on mobile
    if ($(window).width() < 768) {
      console.log('Mobile: Toggling sidebar-open');
      $body.toggleClass('sidebar-open');
    } else {
      // Desktop - use collapse
      console.log('Desktop: Toggling sidebar-collapse');
      $body.toggleClass('sidebar-collapse');
    }
    
    console.log('New classes:', $body.attr('class'));
  });

  // Close sidebar when clicking on a menu item (mobile only)
  if ($(window).width() < 768) {
    $('.sidebar-menu a').on('click', function() {
      $body.removeClass('sidebar-open');
    });
  }

  // Close sidebar when clicking outside (mobile only)
  $(document).on('click', function(e) {
    if ($(window).width() < 768) {
      if (!$(e.target).closest('.main-sidebar').length && 
          !$(e.target).closest('.sidebar-toggle').length &&
          !$(e.target).closest('.navbar').length) {
        $body.removeClass('sidebar-open');
      }
    }
  });

  // Prevent sidebar from sliding when resizing from mobile to desktop
  $(window).resize(function() {
    if ($(window).width() >= 768) {
      $body.removeClass('sidebar-open');
    }
  });
});
</script>