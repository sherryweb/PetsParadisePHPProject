/* 
 *  User registration Validation Process.validate username, password, name, address, email, phone, zipcode infomation.
*/

// Validate the userName 
function checkName() {
    var targetField = document.getElementById('nameinput');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}        

// Validate the first Name 
function checkFirstName() {
    var targetField = document.getElementById('firstName');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}  

// Validate the last Name 
function checkLastName() {
    var targetField = document.getElementById('lastName');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}  


// Validate the Email 
function checkEmail() {
    var targetField = document.getElementById('emailinput');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}        

// Validate the Email 
function checkSubEmail() {
    var targetField = document.getElementById('email');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}        


// Validate the Address 
function checkAddress() {
    var targetField = document.getElementById('addressinput');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}

// Validate the phone 
function checkPhone() {
    var targetField = document.getElementById('phoneinput');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}

// Validate the subject 
function checkSubject() {
    var targetField = document.getElementById('subjectinput');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}

// Validate the subject 
function checkBudget() {
    var targetField = document.getElementById('budgetinput');
    if(targetField.value.length === 0) {
        // if the user hasn't completed the field,
        // change its style background-color to yellow
        targetField.style.backgroundColor = 'yellow';
        targetField.focus();
        return false;
    }
    return true;
}

// The Validation Process called by user registration page(user_registration.html)
function checkAllItems() {
    //alert("Entering Validation");
    // validate the name   
    if (!checkName())
        return false;
    // validate the mail    
    if (!checkEmail())
        return false;
    // validate the address    
    if (!checkAddress())
        return false;                
    // validate the phone    
    if (!checkPhone())
        return false;                
    if (!checkSubject())
        return false;       
    if (!checkBudget())
        return false;                       
    // if all items validation passed , return ture
    //alert("Validated OK");
    return true;
} // End of checkAllItems

/* 
 *  The end of User Registration Validation Process.
*/

/* 
 *  User change contact information Validation Process.validate name, email, address, phone, zipcode infomation.
 *  use the same validation methods for every input fields with user registration proess.
*/
// The Validation Process called by user change contact information page(user_chg_contact.html)
function checkSubscribeItems() {
    // validate the first name   
    if (!checkFirstName())
        return false;
    // validate the last name    
    if (!checkLastName())
        return false;
    // validate the Email    
    if (!checkSubEmail())
        return false;                
     return true;
} // End of checkContactInfoItems

/* 
 *  The end of User change contact information Validation Process.
*/

function checklogin() {
    //var username = $('.username').val();
    //var username = "sssssss";
    alert(username);    
    return false;
} // End of checkContactInfoItems