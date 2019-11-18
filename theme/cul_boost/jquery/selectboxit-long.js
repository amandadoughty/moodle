$(function() {
    $(".formulation select").selectBoxIt({
        theme: "default",
        autoWidth: true
    });

    // Get the width of the question text column before we strtch the table
    // to 100%.
    var origwidth = $(".formulation table.answer  td.text").css("width");
    // Stretch the table width to 100%.
    $(".formulation table.answer").css("width", "100%");
    // Reset the width of the question text column.
    $(".formulation table.answer  td.text").css("width", origwidth);
    // Fit the select box to the space left in the table. NB if this is
    // done in css before the select box is created then the % value is a 
    // % of the browser width as it is created and then used to replace 
    // the original select.
    $(".formulation .selectboxit-container").css("width", "100%");
    //$(".formulation .selectboxit-options").css("width", "100%");
});