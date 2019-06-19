<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="/ovirt-engine/webadmin/theme/00-ovirt.brand/bundled/patternfly/css/patternfly.min.css">
    <link rel="stylesheet" type="text/css" href="/ovirt-engine/webadmin/theme/00-ovirt.brand/bundled/patternfly/css/patternfly-additions.min.css">
    <link rel="stylesheet" type="text/css" href="/ovirt-engine/webadmin/theme/00-ovirt.brand/common.css">
    <link rel="stylesheet" type="text/css" href="/ovirt-engine/webadmin/theme/00-ovirt.brand/webadmin.css">
    <script type="text/javascript" src="/ovirt-engine/webadmin/theme/00-ovirt.brand/bundled/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="/ovirt-engine/webadmin/theme/00-ovirt.brand/bundled/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/ovirt-engine/webadmin/theme/00-ovirt.brand/bundled/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/ovirt-engine/webadmin/theme/00-ovirt.brand/bundled/patternfly/js/patternfly.min.js"></script>
    <style>
      .form-horizontal .control-label.text-left {
        text-align: left;
      }
      .GKGFBNLBERB {
        font-weight: bold;
        margin-bottom: 5px;
      }
      .GKGFBNLBCNB {
        overflow-x: auto;
        margin-top: -1px;
      }
      .content-view-pf-pagination .btn-pagination {
        display: -ms-flexbox;
        display: flex;
        margin: 0 0 0 10px;
      }
      /* dataTables CSS modification & positioning */
      table.dataTable thead {
        position:relative;
        zoom:1;
      }
      
      table.dataTable thead .sorting_asc, 
      table.dataTable thead .sorting_desc {
          color: #6e7989 !important;
          position: relative;
      }
      
      table.dataTable thead .sorting:before,
      table.dataTable thead .sorting_asc:before,
      table.dataTable thead .sorting_desc:before,
      table.dataTable thead .sorting_asc_disabled:before,
      table.dataTable thead .sorting_desc_disabled:before {
        right: 0 !important;
        content: "" !important;
      }
      table.dataTable thead .sorting:after,
      table.dataTable thead .sorting_asc:after,
      table.dataTable thead .sorting_desc:after,
      table.dataTable thead .sorting_asc_disabled:after,
      table.dataTable thead .sorting_desc_disabled:after {
        right: 0 !important;
        content: "" !important;
      }
      table.dataTable thead th {
          position: relative;
          background-image: none !important;
          padding-left: 14px !important;
      }
        
      table.dataTable thead th.sorting:after,
      table.dataTable thead th.sorting_asc:after,
      table.dataTable thead th.sorting_desc:after {
          position: absolute !important;
          top: 50% !important;
          display: block !important;
          line-height: 0.0px !important;
          left: 0 !important;
          font-family: FontAwesome !important;
          font-size: 1.2em !important;
          padding-left: 4px !important;
      }
      table.dataTable thead th.sorting:after {
          content: "\f0dc" !important;
          color: rgba(255, 255, 255, 0) !important;
          font-size: 1.2em !important;
      }
      table.dataTable thead th.sorting_asc:after {
          content: "\f0de" !important;
      }
      table.dataTable thead th.sorting_desc:after {
          content: "\f0dd" !important;
      }
      table.dataTable tbody > tr.selected > td > a {
        color: white;
      }
      table.dataTable tbody > tr > td > a:hover {
        text-decoration:underline;
      }
    </style>
  </head>
  <body>
    <div class="obrand_main_tab container-fluid">
      <div class="row">
        <div class="col-sm-12">
          <ol class="breadcrumb">
              <li class="active">Kubernetes</li>
              <li class="active">Shell</li>
            </ol>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">Panel Heading</div>
        <div class="panel-body">
          <iframe src="console.php" width="250px" height="300px" frameborder="0" scrolling="no"></iframe>
        </div>
      </div>
    </div>
  </body>
</html>
