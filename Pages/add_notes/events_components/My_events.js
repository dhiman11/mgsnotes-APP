 
import React, { Component } from 'react';
import { View, Text, StyleSheet,Image } from 'react-native';
import { TouchableOpacity } from 'react-native-gesture-handler';
 
 
// create a component
class My_events extends Component {

    addContactProduct(event_id){
      this.props.updateState(event_id); 
    }

    render() { 
            return ( 
                <View> 
                    <TouchableOpacity onPress ={() => { this.addContactProduct(this.props.eventid) }}>
                            <Image style ={{width:80,height:70,marginRight:15}} 
                            source={require('../../../assets/img/eventicon.png')}
                            />
                            <Text numberOfLines={1} ellipsizeMode ="tail" style={styles.eventname}>{this.props.eventname}</Text>
                            {/* <Text  >{this.props.arrayid}</Text> */}
                    </TouchableOpacity>
                 
                </View>  
                    
            );
        
    }
}

// define your styles
const styles = StyleSheet.create({
    eventname:{
        
        fontWeight:"bold",
        fontSize:12,
        marginBottom:10,
        flex: 1,
        flexWrap: 'wrap',
        width:90
    }
});

//make this component available to the app
export default My_events;
