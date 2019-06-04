//import liraries
import React, { Component } from 'react';
import { View, Text, StyleSheet,Image } from 'react-native';
import { TouchableOpacity } from 'react-native-gesture-handler'; 

// create a component


class Product_list extends Component {

    constructor(props){
        super(props); 
    }


    __producteditpage=(product_id)=>{
        this.props.updateState(product_id); 
    }

    render() {
        return (
            <View style={styles.box}>   
                <TouchableOpacity onPress={()=>{this.__producteditpage(this.props.data.mysqlarrayP.product_id)}}> 
                    <Image source = {{uri:this.props.data.images.src}} style = {{ width: 180, height: 180 }}   />
                    <Text style={styles.productname}>{this.props.data.mysqlarrayP.product_name}</Text>
                    <Text style={styles.price}>USD {this.props.data.mysqlarrayP.fob_price}</Text>  
                    <Text style={styles.moq}>MOQ {this.props.data.mysqlarrayP.moq}</Text>  
                    <Text style={styles.username}>by: {this.props.data.mysqlarrayP.user_name}</Text> 
                </TouchableOpacity>
            </View>
        );
    }
}

// define your styles
const styles = StyleSheet.create({

    productname:{
        fontSize:16,
        fontWeight: 'bold',
    },
    box:{ 
        marginRight: 20,
        marginBottom: 20,
        justifyContent: 'space-between',
    },username:{
        fontSize:14
    },price:{
        color:"red",
        fontSize:18
    }
    
});

//make this component available to the app
export default Product_list;
