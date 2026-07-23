
// //timeslot
// function add_timeslot(event){
//     event.preventDefault();
//     const formData = new FormData(event.target.closest('form'));
//     formData.append('formType','add_timeslot');
//     $.post('api.php', formData, function(response) {
//         return respose;
//     });
//     return result;
// }

// async function get_one_timeslot(event) {
//     event.preventDefault();
//     const formData = new FormData();
//     formData.append('formType', 'get_one_timeslot');
//     formData.append('slotId', 1);
//     const result = await $.ajax({
//         url: 'api.php',
//         type: 'POST',
//         data: formData,
//         processData: false,
//         contentType: false
//     });
//     return result;
// }


