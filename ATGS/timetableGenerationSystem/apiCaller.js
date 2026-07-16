
//timeslot
async function add_timeslot(event){
    event.preventDefault();
    const formData = new FormData(event.target.closest('form'));
    formData.append('formType','add_timeslot');
    const result = await fetch(
        'api.php',
        {
            method:'POST',
            body:formData
        }
    );
    console.log('Hello');
    return result;
}
async function get_one_timeslot(){}
async function get_all_timeslot(){}
async function update_timeslot(){}
async function delete_timeslot(){}