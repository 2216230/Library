<!-- jQuery 3 -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!-- SlimScroll -->
<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Data Table Initialize -->
<script>
  $(function () {
    function styleRow(row){
      $(row).css({
        'color':'#000',            // black text
        'background-color':'#fff'  // white row
      });
      $(row).hover(function(){
        $(this).css('background-color','var(--primary-dark)');
        $(this).css('color','white');
      }, function(){
        $(this).css('background-color','#fff');
        $(this).css('color','#000');
      });
    }

    $('#example1, #booklist').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : true,
      'ordering'    : true,
      'info'        : false,
      'autoWidth'   : false,
      'createdRow': function(row, data, dataIndex){
        styleRow(row);
      },
      'headerCallback': function(thead, data, start, end, display){
        $(thead).css({
          'background':'var(--primary-dark)',
          'color':'white'
        });
      }
    });

    $('#searchBox').on('keyup', function(){
      $('#booklist').DataTable().search(this.value).draw();
    });
  });
</script>

<style>
  body, .content-wrapper {
    background-color: #ffffff; /* white background */
    color: #000;              /* black text */
  }

  /* Box styling */
  .box {
    border: 3px solid var(--primary-dark); /* dark green border */
    border-radius: 12px;
    overflow: hidden;
    background-color: #fff;    /* white inside */
  }

  /* Box header */
  .box-header {
    background: var(--primary-dark);  /* dark green */
    color: white;
    padding: 15px;
    border-bottom: 3px solid rgba(0,0,0,0.06);
    font-weight: bold;
    font-size: 20px;
  }

  /* Table borders */
  #example1, #booklist {
    border: 2px solid var(--primary-dark);
    border-radius: 8px;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  /* Table alternating rows */
  #example1 tbody tr:nth-child(odd),
  #booklist tbody tr:nth-child(odd) {
    background-color: #fff; /* white */
  }
  #example1 tbody tr:nth-child(even),
  #booklist tbody tr:nth-child(even) {
    background-color: #f2f2f2; /* light gray */
  }

  /* Hover effect */
  #example1 tbody tr:hover,
  #booklist tbody tr:hover {
    background-color: var(--primary-dark) !important; /* dark green */
    color: white;
  }

  /* Header row */
  #example1 thead, #booklist thead {
    background: var(--primary-dark); /* dark green */
    color: white;
  }

  /* DataTables search input and select */
  .dataTables_filter input,
  .dataTables_length select {
    background-color: #fff;
    color: #000;
    border: 2px solid var(--primary-dark);
    border-radius: 5px;
    padding: 4px 8px;
  }

  /* Pagination buttons */
  .pagination>li>a,
  .pagination>li>span {
    background-color: #fff;
    color: var(--primary-dark);
    border: 2px solid var(--primary-dark);
    border-radius: 5px;
  }
  .pagination>li>a:hover,
  .pagination>li>span:hover {
    background-color: var(--primary-dark);
    color: white;
  }
</style>
<script>
window.addEventListener('scroll', function() {
  const nav = document.getElementById('mainNav');
  if (window.scrollY > 50) {
    nav.classList.add('sticky');
  } else {
    nav.classList.remove('sticky');
  }
});
</script>

