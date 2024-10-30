jQuery(document).ready(function($) {
  
  var dataSet = php_vars.js_array;

  $("#example").DataTable({
    "order": [[ 0, "asc" ]],
      data: dataSet,
      columns: [
          { title: "No." },
          { title: "transaction id" },
          { title: "Payee" },
          { title: "amount" },
          { title: "provider" },
          { title: "description" },
          { title: "status" },
          { title: "transaction Date" },
          { title: "completed Date" }
      ]
  });
  
});