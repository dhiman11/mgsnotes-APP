//import liraries
import React, { Component } from 'react';
import {StyleSheet } from 'react-native';
 
import Add_events from './add_notes/Add_event';
 


// create a component
class Add_note extends Component { 
    render() {
        return (  
            
            <Add_events/> 
                
            );
    }
}

 

//make this component available to the app
export default Add_note;
