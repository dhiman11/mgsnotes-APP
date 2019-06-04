//import liraries

import React, { Component } from 'react';
import { View, Text, StyleSheet,Modal,TouchableOpacity,Image } from 'react-native';
import { ScrollView } from 'react-native-gesture-handler';
 
  

// create a component
class Product_detail_modal extends Component {
 
    setModalVisible(visible)
    {
        this.props.updateState(visible);
    }

  
    render() {
        return (
            <View> 

                <Modal
                    animationType="slide"
                    transparent={false}
                    visible={this.props.modalvisibility} 
                    onRequestClose={() => {
                    this.setModalVisible(false)
                    }}>

                    <ScrollView>
                            <View  style={styles.container}> 
                                    {this.props.product_main_pic.src?
                                    <View >  
                                        <Image source = {{uri:this.props.product_main_pic.src}}
                                                style = {{ width: "100%", height: 300 }}
                                                />
                                    </View>:null}

                
                                
                                <View>   
                                        <Text style={styles.product_name}>{this.props.product_data.product_name}</Text>
                                </View> 
                                <View >   
                                        <Text style={styles.fob_price} >$ {this.props.product_data.fob_price}</Text>
                                </View> 
                                <View>   
                                        <Text  style={styles.moq} >MOQ: {this.props.product_data.moq}</Text>
                                </View> 
                                <View>   
                                        <Text  style={styles.parent_supp_eve} >Category: {this.props.product_data.category_name}</Text>
                                </View> 
                                <View>   
                                        <Text  style={styles.parent_supp_eve} >Supplier: {this.props.product_data.supplier_name}</Text>
                                </View> 
                                <View >   
                                        <Text style={styles.parent_supp_eve} >Event name: {this.props.product_data.event_name}</Text>
                                </View> 

                                <View >   
                                        <Text style={styles.note} >Note:</Text>
                                </View>  

                                <View >   
                                        <Text>{this.props.product_data.note}</Text> 
                                </View> 

                                <View >    
                                        <Text style={styles.created_by} >Created by : {this.props.product_data.user_name}</Text>
                                </View> 
                                <View  >    
                                        <Text style={styles.photos}>All PHOTOS: </Text>
                                </View> 
                                 {this.props.all_images ?
                                    <View>
                                        {this.props.all_images.map((item, index) => { 
                                                        return (<View key={index} >
                                                                    <Image  source = {{uri:item}} style = {{ width: "100%", height: 300,marginBottom:10 }} />
                                                                </View>  )    
                                        })}
                                    </View>
                                  :null}
  
                            </View> 
                   </ScrollView>

                </Modal>

            </View>
        );
    }
}

// define your styles
const styles = StyleSheet.create({
    container: {
        flex: 1,
        padding:10,
        backgroundColor: '#fff',
    },
    product_name:{
        fontSize:24,  
        
    },
    fob_price:{ 
        fontSize:22, 
        color:"red",
        fontWeight:"bold"
    },
    parent_supp_eve:{  
        fontSize:20, 
      
    },
    created_by:{
        marginTop:30
    }, 
    moq:{ 
        marginTop:10, 
        marginBottom:10
    },
    note:{
        marginTop:20,
        fontSize:20, 
    },
    photos:{ 
        marginTop:20,  
        marginBottom:10, 
        fontSize:24 
    } 
});

//make this component available to the app
export default Product_detail_modal;
