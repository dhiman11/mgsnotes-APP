//import liraries
import React, { Component } from 'react';
import { View, Text, StyleSheet,TouchableOpacity,Image } from 'react-native';
 

// create a component
class Images_upload_view extends Component {

    render() {
        
        return (
            <View style={{marginTop:20,marginRight:20}}>
               <Image style={{width:100,height:100}} source={{uri: `data:image/jpeg;base64,${this.props.base64img}`}} />
            </View>
        );

    }
}

// define your styles
const styles = StyleSheet.create({
    container:{
        flex:1
    },
    supplierlist:{
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight:10,
        backgroundColor: '#176fc1',
        padding:10 
    }
});

//make this component available to the app
export default Images_upload_view;
