//import liraries
import React, { Component } from 'react';
import { View, FlatList,Text,TextInput, StyleSheet,TouchableOpacity,ScrollView,Alert,Image,Picker } from 'react-native';
import ImagePicker from 'react-native-image-crop-picker';
import AsyncStorage from '@react-native-community/async-storage'; 
import Last_supplier from './Last_supplier.js';
import Images_upload_view from './Images_upload_view.js';
import { SafeAreaView } from 'react-navigation';
 

// create a component
 


class Create_products extends Component {
  
 
    constructor(props){
        super(props);
        //////////////////
        this.state={
            user_id:0,
            user_name:'',
            event_id:0,
            lastsupp:[],
            supplier_name:'',
            supplier_id:0,
            product_name:'',
            supp_ref:'',
            fobusd:'',
            moq:'',
            notes:'',
            product_categories:[],
            product_category:0, 
            images:[],
            response:' '

        }
        ///////////////////
        this.getData();
        this.ifloginalerady();

      
    }

 
    /////////////////GET COOKIES DATA /////////////////////
    ///////////////////////////////////////////////////////
    getData = async () => {
        try {
            const value = await AsyncStorage.getItem('selected_event_id');
            ///////////////////////     
            this.setState({
             event_id:value,
            });
            //////////////////////////
          if(value !== null) {
            // value previously stored
          }
        } catch(e) {
          // error reading value
        }
      }




    ifloginalerady = async()=>{
        try {
         let  token  =  await AsyncStorage.getItem('cookies');
         let data  = JSON.parse(token); 

            if(data.logged_in){
                this.setState({
                    user_id:data.user_id,
                    user_name:data.user_name
                }); 
               //////////////////
               ///////////////// 

             this.con_pro_data(data.user_id); 

             
            }
     

        } catch (error) {
            
        }
    }



    
    con_pro_data=(myuser_id)=>{

        
 
            fetch('http://192.168.1.250/Api/ContactProduct/index', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: myuser_id,
                }),
                }).then((response) => response.json())
                .then((responseJson) => { 
  

                   this.setState({
                    lastsupp : responseJson.supplier_list1,
                    product_categories : responseJson.product_category
                   
                   });
                   
              
                    
                   console.log(this.state.product_categories);
                  
                })
                .catch((error) => {
                // console.error(error);
                });

    }


  
    // Choose images from camera
    ///////////////////////////
    choose_images=()=>{ 

            ImagePicker.openPicker({
                width: 300,
                height: 400,
                compressImageMaxHeight:600,
                compressImageMaxWidth:600,
                multiple:true,
                includeBase64:true,
                compressImageQuality:0.4, 
                cropping: true
              }).then(image => {
 
                    var img_array = [];
                    for (let userObject of image) {
                        img_array.push(userObject.data); 
                    //   console.log(image.height);
                    }
                    // console.log(img_array);
                      this.setState({
                        images: [...this.state.images,...img_array]
                     });

 
              });

            

          
    }

    // Click images from camera \
    ///////////////////////////
    click_images=()=>{
        ImagePicker.openCamera({
            width: 300,
            height: 400,
            multiple:true, 
            compressImageMaxHeight:600,
            compressImageMaxWidth:600,
            compressImageQuality:0.4, 
            includeBase64:true,
            cropping: false
          }).then(image => {
   
               this.setState({
                images: [...this.state.images,image.data]
                });

             console.log(this.state.images);
        

          });
    }



    ////// Set supplier id for the contact
    //////////////////////////////////////
    update_supp_id=(val1)=>{

        this.setState({  
            supplier_id:val1.supplier_id,
            supplier_name:val1.suppliername 
        });
       
    }

    ///////////////////////////////////////
 
    ////// POST Form data//////////////////
    //////////////////////////////////////
    fetchProductdata=()=>{


        this.setState({  
            response:" Note: Please wait we saving your data .... ", 
        });

        fetch('http://192.168.1.250/Api/products/Product/add_products', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
 
        
                user_id : this.state.user_id, 
                user_name : this.state.user_name, 
                event_id : this.state.event_id, 
                pro_sup : this.state.supplier_name,
                supp_id : this.state.supplier_id,
                proname : this.state.product_name,
                proref : this.state.supp_ref,
                profob : this.state.fobusd,
                product_category : this.state.product_category,
                moq : this.state.moq,
                pronote : this.state.notes,
                images : this.state.images,
               

 
  
            }),
            }).then((response) => response.json())
            .then((responseJson) => { 
                console.log( JSON.stringify(responseJson))
            
                if(responseJson.response.result){
                    this.setState({  
                        response:"Note:- Data is saved ",
                        images:[]
                       
                    });
                     
                    this._textInput1.setNativeProps({ text: '' });
                    this._textInput2.setNativeProps({ text: '' });
                    this._textInput3.setNativeProps({ text: '' });
                    this._textInput4.setNativeProps({ text: '' });
                    this._textInput5.setNativeProps({ text: '' });

                    this.con_pro_data(this.state.user_id); 


                }else
                {
                    
                 
                    Alert.alert(
                        'Caution',
                        responseJson.response.msg,
                        [ 
                          {text: 'OK', onPress: () => console.log('OK Pressed')},
                        ],
                        {cancelable: false},
                      );
                    
                    ///////////Status on datanot saved ///////////////////// 
                    this.setState({
                        response:"Data is not saved . Please try again .", 
                    });

                    
                    this.setTimeout( () => { 
                        this.setState({  
                            response:" ", 
                        });
                     },4000);

                }
               
                
                this.setTimeout( () => {
                    this.setState({  
                        response:" ", 
                    });
                 },4000);

                  
            })
            .catch((error) => {
             // console.error(error);
            });
    }


    render() { 
       
        return (
           
            <SafeAreaView style={styles.container}>
               <ScrollView>
                    <View >  
                                
                                <View>
                                <Text style={{fontSize:22,marginBottom:10,fontWeight:"bold",color:"#000000"}}>CERATE PRODUCT</Text> 
                                <FlatList 
                                    numColumns={3} 
                                    data={this.state.lastsupp} 
                                    //Item Separator View this.state.lastsuppliers
                                    renderItem={({item }) => ( 

                                        <Last_supplier update_supp_id={this.update_supp_id.bind(this)}  suppliername={item.supplier_name}  supplier_id= {item.supplier_id} data={item} /> 
                                    
                                    )}

                                    ListFooterComponent={this.renderFooter}
                                    keyExtractor={(item, index) => index.toString()}

                                    />


                                    <TextInput 
                                        style={styles.inputfields} 
                                        textAlign="left" 
                                        onChangeText={(text) => this.setState({supplier_name:text})}
                                        placeholderTextColor='#818181' 
                                        placeholder="Supplier Name*"
                                        value={this.state.supplier_name}
                                    /> 
                                </View>
                             
                                <View>
                                    <TextInput 
                                        style={styles.inputfields} 
                                        textAlign="left" 
                                        onChangeText={(text) => this.setState({product_name:text})}
                                        placeholderTextColor='#818181'
                                        placeholder="Product Name*"
                                        ref={component => this._textInput1 = component}
                                    /> 
                                </View> 
                                <View>
                                    <TextInput 
                                        style={styles.inputfields} 
                                        textAlign="left" 
                                        onChangeText={(text) => this.setState({supp_ref:text})}
                                        placeholderTextColor='#818181'
                                        placeholder="Supp. Ref"  
                                        ref={component => this._textInput2 = component}
                                   
                                    /> 
                                </View>
                                <View>
                                    <TextInput 
                                        style={styles.inputfields} 
                                        textAlign="left" 
                                        onChangeText={(text) => this.setState({fobusd:text})}
                                        placeholderTextColor='#818181'
                                        placeholder="FOB USD"
                                        keyboardType={'phone-pad'}
                                        ref={component => this._textInput3 = component}
                                    /> 
                                </View>
                                <View>
                                    <TextInput 
                                        style={styles.inputfields} 
                                        textAlign="left" 
                                        onChangeText={(text) => this.setState({moq:text})}
                                        placeholderTextColor='#818181'
                                        placeholder="MOQ"
                                        keyboardType={'phone-pad'}
                                        ref={component => this._textInput4 = component}
                                    /> 
                                </View>

                                <View>
                                    <TextInput 
                                        multiline={true}
                                        numberOfLines={10} 
                                        style={styles.textarea} 
                                        textAlign="left" 
                                        onChangeText={(text) => this.setState({notes:text})}
                                        placeholderTextColor='#818181'
                                        placeholder="Notes"
                                        ref={component => this._textInput5 = component}
                                    /> 
                                </View>
                                <View>
                                     <Text style={styles.response}>{this.state.response}</Text>   
                                </View>

                                <View>
                                     <Text>Product Category</Text>   
                                </View>

                                <View>
                                    <Picker 
                                        mode="dropdown"
                                        selectedValue={this.state.product_category}
                                        onValueChange={(value)=>{
                                            this.setState({
                                                product_category: value
                                            }); 
                                         }} >   
                                        {this.state.product_categories.map((item, index) => { 
                                           return (<Picker.Item label={item.category_name} value={item.id} key={index} />)   
                                        })}
                                   </Picker>
                                </View>
                                 
                                <View style={styles.buttons}>

                                <View style={styles.button}>
                                <TouchableOpacity
                                    onPress={() => {this.choose_images()}}
                                    style={{width:40}}>
                                    <Image 
                                        source={require('../../../assets/img/gallery.png')}
                                        style ={{width:40,height:40,marginLeft:8  }}  
                                        />
                                        {/* <Text style={{color:"#000000"}}> GALLERY</Text> */}
                                    </TouchableOpacity>
                                </View> 

                                <View style={styles.button}>
                                <TouchableOpacity
                                    onPress={() => {this.click_images()}}
                                    style={{width:40 }}>
                                        <Image 
                                        source={require('../../../assets/img/camera.png')}
                                        style ={{width:40,height:40,marginLeft:8  }}  
                                        />
                                        {/* <Text style={{color:"#000000"}}>CAMERA</Text> */}
                                    </TouchableOpacity>
                                </View> 

                                <View style={styles.button}>
                                <TouchableOpacity
                                    onPress={() => {this.fetchProductdata()}}
                                    style={{ textAlign:"center",backgroundColor:"#176fc1",width:100,paddingLeft:20,paddingRight:20,paddingTop:10,paddingBottom:10}}>
                                        <Text style={{color:"#fff", textAlign:"center" }}>SAVE</Text>
                                    </TouchableOpacity>
                                </View> 
                                </View>
                                
                                    <View>
                                        <FlatList 
                                        numColumns={3}  
                                        data={this.state.images} 
                                        //data={this.state.images} 
                                        //Item Separator View this.state.lastsuppliers
                                        renderItem={({item }) => (  
                                            <Images_upload_view  base64img={item} />  
                                        )} 
                                        ListFooterComponent={this.renderFooter}
                                        keyExtractor={(item, index) => index.toString()}

                                    />   
                                    </View>

                    </View>
                    </ScrollView>     
            </SafeAreaView>
        );
    }
}

// define your styles
// define your styles
const styles = StyleSheet.create({
    container: {
        flex: 1,
        margin: 20,
        backgroundColor: '#fff',
    },
    buttons:{
       alignItems:"flex-end", 
       textAlign:"center",
        marginTop:5,
        flexDirection: 'row',
        flexWrap: 'wrap',
        
    },
    button:{
        width: '20%' 
    },
 
    inputfields:{
        alignSelf: 'stretch',
        opacity:0.4,
        marginTop: 15,
        fontSize:18,
        backgroundColor:"#fff",  
        maxHeight: 40,
        minWidth: 300,   
        borderColor: "gray",
        borderWidth: 1,
        paddingLeft:15
    },
    textarea:{
        alignSelf: 'stretch',
        opacity:0.4,
        marginTop: 15,
        fontSize:18,
        backgroundColor:"#fff",  
        maxHeight: 150,
        minWidth: 300,   
        borderColor: "gray",
        borderWidth: 1,
        paddingLeft:15,
        textAlignVertical: 'top',
    },
    response:{
        fontSize:20,
        color:"red",
        marginBottom:0
    },
    heading:{
        fontSize:18,
        fontWeight:"normal",
        borderColor: "#176fc1",
        borderWidth: 1,
        backgroundColor:"#176fc1",
        color:"#fff",
        padding:5,
        marginBottom:10 
    }
});
 

//make this component available to the app
export default Create_products;
