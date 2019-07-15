var KUBERNETES_PLUGIN_MESSAGE_PREFIX = 'kubernetes-plugin';
var KUBERNETES_PLUGIN_MESSAGE_DELIM = ':';

function getNamespaces(){
  $.ajax({
    url: "function.php?f=getNamespaces",
    method: "GET",
    success: function(result) {
      var namespaces = JSON.parse(result);
      for ( var i in namespaces.data) {
        var name = namespaces['data'][i]['name'];
        $('#namespaces').append('<option value="' + name + '">' + name + '</option>');
      }
      $('select option[value="default"]').prop('selected',true);
      getPodsList();
    }
  });
}

function getNamespacesList(){
  var namespaces_Table = $("#namespaces_table").DataTable({
    "ajax": {
      url: "function.php?f=getNamespaces",
      type: "GET"
    },
    "columns": [
       {
         "data": "status",
         "render": function ( data, type, row, meta ) {
           if (data == "Active") {
             return '<span class="glyphicon glyphicon-triangle-top" style="color: #00f452; font-size: 11pt;"></span>';
           } else if (data == "Terminating") {
             return '<span class="pficon pficon-locked" style="color: #ff2c2a; font-size: 11pt;"></span>';
           }
         }
       },
       {
         "data": "name",
         "render": function ( data, type, row, meta ) {
           return '<a href="/ovirt-engine/webadmin/?#hosts-general;name='+data+'" target="_parent">'+data+'</a>';
         }
       },
       { 
         "data": "labels",
         "render": function ( data, type, row, meta ) {
             if (data) {
               var labels='';
               for (name in data) {
                 labels += '<span class="label label-primary">' + name + ': ' + data[name] + '</span>&nbsp;';
               }
               return labels;
             } else {
               return '-';
             }
         }
       },
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
    rowId: 'name',
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
    ],
    "initComplete": function(settings, json) {
      var table = $("#namespaces_table").DataTable();
      var unselect = ['default', 'kube-node-lease', 'kube-public', 'kube-system'];
      var names = table.column(1).data();
      $(names).each(function(index, value) {
        if ($.inArray(value, unselect) !== -1) {
          $(table.row(index).node()).addClass('not-selectable');
        }
      });

      table.on('select', function(e, dt, type, indexes) {
        if (type === 'row') {
          var rows = table.rows(indexes).nodes();
               
          $.each($(rows), function() {
            if ($(this).hasClass('not-selectable')) table.row($(this)).deselect();
          })
        }
      });
    }
  });

  $('#SearchPanelView_searchStringInput').keyup(function(){
    namespaces_Table.search($(this).val()).draw();
  });

  $('#SearchPanelView_searchClean').click(function(){
    $('#SearchPanelView_searchStringInput').val('');
    namespaces_Table.search('').draw();
  });

  namespaces_Table.on( 'select.dt deselect.dt', function (){
    var rows = namespaces_Table.rows( { selected: true } ).indexes().length;
    if(rows === 0){
      $('#deleteBtn').attr('disabled', true);
    } else {
      $('#deleteBtn').attr('disabled', false);
    }
  });
}

function createNamespace() {
  $.ajax({
    url: "function.php?f=createNamespace",
    method: "POST",
    data: { comment: $('#newComment').val() },
    success: function(result) {
      if (result == 'ok') {
        alert('Create Namespace Successfully.');
        $('#newAlert').hide('fade');
        $('#newModal').modal('hide');
        $('#newComment').val('');
        $('#namespaces_table').DataTable().ajax.reload();
      } else {
        $('#newAlert div').html(result);
        $('#newAlert').show('fade');
      }
    }
  });
}

function deleteNamespace() {
  var table = $("#namespaces_table").DataTable();
  var rows = table.rows( { selected: true } ).indexes();
  var namespaces = table.rows(rows).data().pluck('name').toArray();
  $.ajax({
    url: "function.php?f=deleteNamespace",
    method: "POST",
    data: { namespaces: namespaces },
    success: function(result) {
      alert('Has been released to delete the namespace(s).\nPlease reflash table to check.');
      table.ajax.reload();
      table.rows('.selected').deselect();
      $('#deleteModal').modal('hide');
    }
  });
}

function getPodsList(){
  var namespace = $('#namespaces').val();
  var pods_Table = $("#pods_table").DataTable({
    "ajax": {
      url: "function.php?f=getPodsList",
      type: "POST",
      data: function(d){ 
        d.namespace = $('#namespaces').val(); 
      }
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
           return '<a href="pod.php?namespace='+row.namespace+'&name='+data+'">'+data+'</a>';
         }
       },
       { "data": "namespace" },
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
    rowId: 'pod_ip',
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

  pods_Table.on( 'select.dt deselect.dt', function (){
    var rows = pods_Table.rows( { selected: true } ).indexes().length;
    if(rows === 0){
      $('#editBtn').attr('disabled', true);
      $('#deleteBtn').attr('disabled', true);
      $('#consoleBtn').attr('disabled', true);
    } else if(rows === 1){
      $('#editBtn').attr('disabled', false);
      $('#deleteBtn').attr('disabled', false);
      $('#consoleBtn').attr('disabled', false);
    } else {
      $('#editBtn').attr('disabled', true);
      $('#deleteBtn').attr('disabled', false);
      $('#consoleBtn').attr('disabled', true);
    }
  });
}

function createPod(students) {
  $.ajax({
    url: "function.php?f=createPod",
    method: "POST",
    data: { namespace: $('#namespaces').val(), comment: $('#newComment').val(), students: students },
    success: function(result) {
      if (result == 'ok') {
        alert('Create Pod Successfully.');
        $('#newAlert').hide('fade');
        $('#newModal').modal('hide');
        $('#newComment').val('');
        $('#pods_table').DataTable().ajax.reload();
      } else {
        $('#newAlert div').html(result);
        $('#newAlert').show('fade');
      }
    }
  });
}

function getPodYaml(pod, namespace) {
  $.ajax({
    url: "function.php?f=getPodYaml",
    method: "POST",
    data: { pod: pod, namespace: namespace },
    success: function(result) {
      var obj = JSON.parse(result);
      var json = JSON.stringify(obj, undefined, 2);
      $('#editComment').val(json);
    }
  });
}

function editPod(pod, namespace) {
  $.ajax({
    url: "function.php?f=editPod",
    method: "POST",
    data: { pod: pod, namespace: namespace, data: $('#editComment').val() },
    success: function(result) {
      if (result == 'ok') {
        alert('Edit Pod Successfully.');
        $('#editModal').modal('hide');
        $('#pods_table').DataTable().ajax.reload();
      } else {
        alert(result);
        $('#newAlert').show('fade');
      }
    }
  });
}

function deletePod(pods, namespaces) {
  $.ajax({
    url: "function.php?f=deletePod",
    method: "POST",
    data: { pods: pods, namespaces: namespaces },
    success: function(result) {
      alert('Has been released to delete the pod(s).\nPlease reflash table to check.');
      $('#pods_table').DataTable().ajax.reload();
      $('#pods_table').DataTable().rows('.selected').deselect();
      $('#deleteModal').modal('hide');
    }
  });
}

function validateForm(c) {
  var e = '';
    if ($('#'+c).val() == '') {
      e = 'false';
      $('#'+c).parents('.form-group').addClass('has-error');
    } else {
      $('#'+c).parents('.form-group').removeClass('has-error');
    }
  return e;
}

function getCurriculumName() {
  $.ajax({
    url: "function.php?f=getCurriculumName",
    method: "GET",
    success: function(result) {
      var data = JSON.parse(result);
      $('#curriculum').append('<option value="null">Choose a curriculum</option>');
      for (var i=0; i<data.length; i++) {
        $('#curriculum').append('<option value="'+data[i].id+'">'+data[i].name+'</option>');
      }
    }
  });
}

function addPodList(namespace, c_select) {
   var table = $("#add_table").DataTable({
    "ajax": {
      url: "function.php?f=addPodList",
      type: "POST",
      data: function(d){
        d.namespace = namespace;
        d.classId = c_select;
      }
    },
    "columns": [
       {
         "data": "id",
         "render": function ( data, type, row, meta ) {
           return '<input type="checkbox" value="'+data+'">';
         }
       },
       { "data": "account" },
       { "data": "name" },
    ],
    "dom": "<'content-view-pf-pagination clearfix'"+
           "<'form-group'B>"+
           "<'form-group'<i><'btn-group btn-pagination'p>>>t",
    "pagingType": "simple_numbers",
    "pageLength": 10,
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
    "columnDefs": [
      {
        targets: 0,
        orderable: false
      }
    ],
    select: {
      items: 'row',
      style: 'multi',
      selector: 'td:first-child input:checkbox'
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

  $('#addTable_search').keyup(function(){
    table.search($(this).val()).draw();
  });

  $('#addTable_searchClean').click(function(){
    $('#addTable_search').val('');
    table.search('').draw();
  });
}
