$(function(){
  $('.datatable').DataTable({
      "lengthMenu": [ [50, 100, 150, 200, 250], [50, 100, 150, 200, "All"] ],
      "pageLength": 200,
      'bPaginate': false,
      "aaSorting": []
  });
  // $('.datatable').css({'border-collapse':'collapse !important'});
  $('.datatable').attr('style', 'border-collapse: collapse !important');
});
