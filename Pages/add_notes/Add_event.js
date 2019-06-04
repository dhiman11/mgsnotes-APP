 
import React, { Component } from 'react';
import {View, Text, StyleSheet,FlatList,Image } from 'react-native';
import Add_contact_products from './Add_contact_products';
import AsyncStorage from '@react-native-community/async-storage';
import My_events from './events_components/My_events';
import { TouchableOpacity } from 'react-native-gesture-handler';
import Add_event_modal from '../modals/Add_event_modal';

 
// create a component
class Add_events extends Component {

    constructor(props){ 
        super(props);

        this.state={
            selected_event_id:null,
            user_id:null,
            create_event_modal:false,
            create_con_pro:false,
            Events_list:[],
            headername:null
        }
 
       this.getData();
        ////////////////

    }
    
 
    getData = async (props) => {
        const value = await AsyncStorage.getItem('cookies')
        try {
        
          if(value !== null) {
           var data = JSON.parse(value);
           this.setState({ 
            user_id :  data.user_id 
        });
        ////////////////////////////
        this.Event_page_data(this.state.user_id);
          
          }
        } catch(e) {
          // error reading value
        }
      }


    /////////////////////////////
    ////////////////////////////
    Event_page_data = (userid) => { 
         
  
        fetch('http://192.168.1.250/Api/Events/index', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userid,
            }),
            }).then((response) => response.json())
            .then((responseJson) => { 

                    this.setState({
                        Events_list: responseJson.interval_list_select,
                        Last_events_list: responseJson.interval_list,
                        headername: responseJson.header_name
                    });

                 

            })
            .catch((error) => {
             // console.error(error);
            });
        }
   ////////////////////////////////////////////////
   //////////////////////////////////////////////
   _c_event_pop=(value)=>{

        this.setState({
            create_event_modal:value
        });
        console.log('hiiiii'); 
     
        this.Event_page_data(this.state.user_id);
    }

 
    ////////////////////////////
    ///////////////////////////
    _c_con_pro_pop=(value)=>{
        this.setState({
            create_con_pro:value
        });
    }
 
    ////Contact Product Popup ////////
    ////////////////////////////
    notePopup=(event_id)=>{  
        this.setState({  
            create_con_pro:true,
            selected_event_id:event_id
        });
        

        this.storeData(event_id);
    }
    //////////////////////////
    /////////////////////////
    /////// Store selected_event_id ///////
    storeData = async (event_id) => {
        try {
          await AsyncStorage.setItem('selected_event_id', event_id)
          console.log('event_id saved'+event_id); 
        } catch (e) {
          // saving error
        }
      }


    render() { 
        
        return (
            <View  style={styles.container} >
              {this.state.create_event_modal?<Add_event_modal updateState={this._c_event_pop.bind(this)} modalvisibility ={this.state.create_event_modal}/>:null}
            
              {this.state.create_con_pro? <Add_contact_products selected_event_id = {this.state.selected_event_id} updateState = {this._c_con_pro_pop.bind(this)} modalvisibility ={this.state.create_con_pro} />:null}

                {/* <View>
                    <Text style={{fontSize:22,marginBottom:10,fontWeight:"bold"}}>MY EVENTS</Text>
                </View> */}
                <View>
                    <TouchableOpacity onPress ={() => { this._c_event_pop(true) }} >
                        <Image style ={{width:60,height:60,marginRight:15}} 
                        source={require('../../assets/img/event_add.png')}
                        />
                        <Text style={styles.eventname}>ADD EVENT</Text>
                    </TouchableOpacity>
                    <Text style={styles.heading}>RECENT EVENTS</Text>
                    <FlatList 
                            numColumns={3} 
                            data={this.state.Last_events_list} 
                            //Item Separator View
                            keyExtractor={(item, index) => index.toString()} 
                            renderItem={({item , index }) => ( 
                                <My_events  updateState={this.notePopup.bind(this)} eventid= {item.event_id} eventname = {item.event_name}/>
                              )} 
                    />
                  
                </View>
                <Text style={styles.heading}>ALL EVENTS</Text>
                    <FlatList 
                            numColumns={4}
                            data={this.state.Events_list} 
                            //Item Separator View
                            keyExtractor={(item, index) => index.toString()} 
                            renderItem={({item , index }) => ( 
                                <My_events updateState={this.notePopup.bind(this)} eventid= {item.event_id} eventname = {item.event_name}/>
                             
                            )}
         
                    />
                 
            </View>
        );
    }
}

// define your styles
const styles = StyleSheet.create({
    container: {
        flex: 1 ,
        margin: 20,
        
    },
    heading:{
        fontSize:22,
        color:"#000000",
        marginTop: 10,
        fontWeight:"bold"
    }
});

//make this component available to the app
export default Add_events;
