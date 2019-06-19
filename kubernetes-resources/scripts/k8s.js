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

  var pods_Table = $("#pods_table").DataTable();
  pods_Table.on( 'select.dt deselect.dt', function (){
    var rows = pods_Table.rows( { selected: true } ).indexes().length;
    if(rows === 0){
      $('#deleteBtn').attr('disabled', true);
      $('#consoleBtn').attr('disabled', true);
    } else if(rows === 1){
      $('#consoleBtn').attr('disabled', false);
      $('#deleteBtn').attr('disabled', false);
    } else {
      $('#consoleBtn').attr('disabled', true);
      $('#deleteBtn').attr('disabled', false);
    }
  });

  $('#deleteBtn').click(function(){
    var rows = pods_Table.rows( { selected: true } ).indexes();
    var names = pods_Table.rows(rows).data().pluck('name').toArray();
    $('#deleteItems').html('');
    for (name of names) {
      $('#deleteItems').append("<div>- " + name + "</div>");
    }
  });

  $('#deleteModal button[type=submit]').click(function(){
    $('#deleteModal').modal('hide');
    var rows = pods_Table.rows( { selected: true } ).indexes();
    var names = pods_Table.rows(rows).data().pluck('name').toArray();
    deletePod(names);
  });
  $('#consoleBtn').click(function(){
    var rows = pods_Table.rows( { selected: true } ).indexes();
    var name = pods_Table.rows(rows).data().pluck('name').toArray()[0];
    $(location).attr('href','shell.php?name='+ name);
  });
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
       { "data": "status" },
       { "data": "uptime" }
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
    select: {
      items: 'row'
    },
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

function deletePod(pods) {
  $.ajax({
    url: "function.php?f=deletePod",
    method: "POST",
    data: { pods: pods },
    success: function(result){
      alert('Has been released to delete the pod(s).\nPlease reflash table to check.');
      $('#deleteModal').modal('hide');
    }
  });
}
