function changeuri() { 
            let stateObj = { id: "100" }; 
            window.history.replaceState(stateObj, 
                        "403 Forbidden", "/Membership/NotApproved?uid="+Math.floor((Math.random() * 100000000) + 10000)); 
        } 
        
 changeuri();
