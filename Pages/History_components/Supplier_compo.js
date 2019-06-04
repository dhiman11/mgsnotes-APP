
import React, { Component } from 'react';
import { View, Text, StyleSheet,Image } from 'react-native';

class Supplier_compo extends Component {
 
    render() {
        return (
            <View style={styles.suppliers}>

                <View style={styles.supp_div}>

                    <View style={{flexDirection:"row"}}>
                        <View style={{width:"25%"}}> 
                            <Image source={require('../../assets/img/factory.png')} style = {{ width: 80, height: 80 }}   />
                        </View>  

                        <View  style={{width:"75%"}}>   
                            <View>
                                <Text style={styles.title}>Supplier : {this.props.data.supplier_name}</Text>
                            </View>

                            <View>
                                <Text>Event :{this.props.data.event_name}</Text> 
                            </View>

                            <View>
                                <Text>Created by : {this.props.data.user_name}</Text> 
                            </View>
                            <View>
                                <Text>Created on : {this.props.data.creation_date}</Text> 
                            </View>
  
                        </View>
                    </View>

                </View>
 
            </View> 
        )  
    }


}
 

// define your styles
const styles = StyleSheet.create({
    suppliers: { 
        flex: 1, 
        backgroundColor: 'red',
        color:"#fff",
        borderBottomColor: 'gray',
        borderBottomWidth: 1, 
        justifyContent: "flex-end", 
        alignItems: "flex-end",
        backgroundColor: '#fff',
    }, 
    supp_div:{
        width:"100%",
        padding:10
    },
    title:{
        fontSize:15,
        fontWeight:"bold"
    }

 
});

//make this component available to the app
export default Supplier_compo;

