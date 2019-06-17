$(document).ready(function(){
  $.fn.dataTable.ext.classes.sPageButton = 'btn btn-default GKGFBNLBANB';
  getPodsList();
  $("#newForm").submit(function(event) {
    event.preventDefault();
    if (validateNewForm() != 'false') {
      createPod();
    }
  });
  $('#newAlertClose').click().hide('fade');
});

var KUBERNETES_PLUGIN_MESSAGE_PREFIX = 'kubernetes-plugin';
var KUBERNETES_PLUGIN_MESSAGE_DELIM = ':';

function getPodsList(){
  var pods_Table = $("#pods_table").DataTable({
    "ajax": {
      url: "function.php?f=getPodsList",
    },
    "columns": [
       {
         "data": "status",
         "render": function ( data, type, row, meta ) {
           if (data == "Running") {
             return '<span class="glyphicon glyphicon-triangle-top" style="color: #00f452; font-size: 11pt;"></span>';
           } else if (data == "Pending") {
             return '<span class="pficon pficon-builder-image" style="color: #ff2c2a; font-size: 11pt;"></span>';
           }
         }
       },
       {
         "data": "name",
         "render": function ( data, type, row, meta ) {
           return '<a href="/ovirt-engine/webadmin/?#hosts-general;name='+data+'" target="_parent">'+data+'</a>';
         }
       },
       { "data": "host_ip" },
       { "data": "pod_ip" },
       { "data": "restarts" },
       { "data": "container_name" },
       { "data": "container_image" },
       { "data": "status" }
    ],
    "dom": "<'content-view-pf-pagination clearfix'"+
           "<'form-group'B>"+
           "<'form-group'<i><'btn-group btn-pagination'p>>>t",
    "pagingType": "simple",
    "pageLength": 100,
    "language": {
      "zeroRecords": "No matching records found",
      "info": "_START_ - _END_",
      "paginate": {
        "previous": '<i class="fa fa-angle-left"></i>',
        "next": '<i class="fa fa-angle-right"></i>'
      },
      "select": {
        rows: ""
      }
    },
    "order": [[ 1, "asc" ]],
    "columnDefs": [{
      "targets": 0,
      "orderable": false 
    }],
    select: true,
    buttons: [
      {
        "text": '<i class="fa fa-refresh"></i>',
        "className": 'btn btn-default',
        "action": function ( e, dt, node, config ) {
          dt.ajax.reload();
        }
      }
    ]
  });
  $('#SearchPanelView_searchStringInput').keyup(function(){
    pods_Table.search($(this).val()).draw();
  });
  $('#SearchPanelView_searchClean').click(function(){
    $('#SearchPanelView_searchStringInput').val('');
    pods_Table.search('').draw();
  });
}
function validateNewForm() {
  var e = '';
    if ($('#comment').val() == '') {
      e = 'false';
      $('#comment').parents('.form-group').addClass('has-error');
    } else {
      $('#comment').parents('.form-group').removeClass('has-error');
    }
  return e;
}
function createPod() {
  $.ajax({
    url: "function.php?f=createPod",
    method: "POST",
    data: { comment: $('#comment').val() },
    success: function(result){
      if (result == 'ok') {
        alert('Create Pod Successfully.');
        $('#newAlert').hide('fade');
        $('#newModal').modal('hide');
        $('#pods_table').DataTable().ajax.reload();
      } else {
        $('#newAlert div').html(result);
        $('#newAlert').show('fade');
      }
    }
  });
}
