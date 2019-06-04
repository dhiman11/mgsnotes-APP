//import liraries

import React, { Component } from 'react';
import { View, Text, StyleSheet,Modal,TouchableOpacity } from 'react-native';
import { TextInput  } from 'react-native-gesture-handler';
import DateTimePicker from "react-native-modal-datetime-picker"; 
 
 
 
 

// create a component
class Add_event_modal extends Component {

    state={
        isDateTimePickerVisible: false,
        fairnew:'',
        city:'',
        fromdate:'-',
        todate:'-',
        user_id:1
    }


    setModalVisible(visible)
    {
        this.props.updateState(visible);
    }

    /////////////////////
    //// DATES ////////

    hideDateTimePicker = () => {
        this.setState({ isDateTimePickerVisible: false });
    };

    confirmed = date => {  

        var date = new Date(date); 
        var date = date.getFullYear()+"-"+date.getMonth()+"-"+date.getDate();

          if(this.state.selecting_date_for =="fromto"){ 
        
            this.setState({
                fromdate: date.toString()
            }); 
          }
         if(this.state.selecting_date_for =="todate"){ 
            this.setState({
                todate: date.toString()
            }); 
          }
        this.hideDateTimePicker();
    };


    showDateTimePicker = (datetype) => {  
        this.setState({ 
            isDateTimePickerVisible: true ,
            selecting_date_for:datetype
        });
      };



      submitForm=()=>{
        //////////////////////////
        ///////////////////////// 
      
        let fairnew = this.state.fairnew;
        let city = this.state.city;
        let fromdate = this.state.fromdate;
        let todate = this.state.todate;
        let user_id = this.state.user_id;
       
      
        fetch('http://192.168.1.250/Api/Events/addevent', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            }, 
            body: JSON.stringify({
                fairnew:fairnew,
                city:city,
                fromdate:fromdate,
                todate:todate,
                user_id:user_id,
            }),
            }).then((response) => response.json())
            .then((data) => {
                 
               
                   console.log(data.last_inserted_id);
               
                   this.setModalVisible(false)
               
              
            })
            .catch((error) => {
             // console.error(error);
            });

      }

    /////////////////////
    //// DATES ////////

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
                    <View style={styles.container}> 
                        <Text style={{fontSize:22,marginBottom:10,fontWeight:"bold",color:"#000000"}}>CERATE EVENT</Text>
                  
                        <View>
                            <TextInput 
                            style={styles.inputfields} 
                             textAlign="left" 
                             onChangeText={(text) => this.setState({fairnew:text})}
                             placeholderTextColor='#818181'
                             placeholder="Name of new event"
                            /> 
                        </View>

                        <View>
                            <TextInput 
                            style={styles.inputfields} 
                             textAlign="left" 
                             onChangeText={(text) => this.setState({city:text})}
                             placeholderTextColor='#818181'
                             placeholder="(City of New Event)"
                            /> 
                        </View>
                        <View>
                            <Text> </Text>
                        </View>
                        <View>
                            <TouchableOpacity  
                            onPress={() => {this.showDateTimePicker('fromto')}} 
                            style={styles.datebutton}
                            > 
                            <Text  style={{fontSize:23}}>From date : {this.state.fromdate}</Text>
                             </TouchableOpacity>
                      </View> 

                      <View>
                            <TouchableOpacity  
                            onPress={() => {this.showDateTimePicker('todate')}} 
                            style={styles.datebutton}
                            >

                            <Text style={{fontSize:23}} >To date {this.state.todate}</Text>

                            </TouchableOpacity>
                      </View>  
                        <View>
                            
                            <DateTimePicker
                                isVisible={this.state.isDateTimePickerVisible}
                                onConfirm={this.confirmed}
                                onCancel={this.hideDateTimePicker}
                            />   
                        </View>
                        <View style={{alignItems:"center",alignContent:"center"}}>
                            <TouchableOpacity
                             onPress={() => {this.submitForm()}}
                             style={{backgroundColor:"#176fc1",width:100,paddingLeft:20,paddingRight:20,paddingTop:10,paddingBottom:10}}>
                                <Text style={{color:"#fff"}}>CREATE</Text>
                            </TouchableOpacity>
                        </View>


                   </View>
                </Modal>
            </View>
        );
    }
}

// define your styles
const styles = StyleSheet.create({
    container: {
        flex: 1,
        margin: 20,
        backgroundColor: '#fff',
    },
    datebutton:{
        alignSelf: 'stretch',
        opacity:0.4,
        marginBottom: 15,
        fontSize:22,
        backgroundColor:"#fff",  
        maxHeight: 50,
        minWidth: 300,   
        borderColor: "gray",
        borderWidth: 1,
        paddingLeft:15,
        paddingTop: 8,
        paddingBottom: 15
        
        
    },  
    inputfields:{
        alignSelf: 'stretch',
        opacity:0.4,
        marginTop: 15,
        fontSize:22,
        backgroundColor:"#fff",  
        maxHeight: 50,
        minWidth: 300,   
        borderColor: "gray",
        borderWidth: 1,
        paddingLeft:15
    },
});

//make this component available to the app
export default Add_event_modal;
