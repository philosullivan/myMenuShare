$(document).foundation()

//
$('.loader').click(function(e) {
    var url = $(this).attr('href');
    $('#offCanvasLeft').foundation('close');
    e.preventDefault();

    $.LoadingOverlay('show', {
        background: 'rgba(37, 37, 34, 0.8)',
        imageColor: 'white',
    });
    window.setTimeout(function() {
        window.location.href = url;
        $.LoadingOverlay( 'hide', true );
    }, 850);
});

// Abide form validation listener.
$(document)
  // field element is invalid
  .on("invalid.zf.abide", function(ev,elem) {
    console.log("Field id "+ev.target.id+" is invalid");
  })
  // field element is valid
  .on("valid.zf.abide", function(ev,elem) {
    console.log("Field name "+elem.attr('name')+" is valid");
  })
  // form validation failed
  .on("forminvalid.zf.abide", function(ev,frm) {
    console.log("Form id "+ev.target.id+" is invalid");
  })
  // form validation passed, form will submit if submit event not returned false
  .on("formvalid.zf.abide", function(ev,frm) {
    console.log("Form id "+frm.attr('id')+" is valid");
    // ajax post form
  })
  // to prevent form from submitting upon successful validation
  .on("submit", function(ev) {
    // ev.preventDefault();
    console.log("Submit for form id "+ev.target.id+" intercepted");
  });
// You can bind field or form event selectively
/*
$("#foo").on("invalid.zf.abide", function(ev,el) {
  alert("Input field foo is invalid");
});
$("#bar").on("formvalid.zf.abide", function(ev,frm) {
  alert("Form is valid, finally!");
  // do something perhaps
});
*/