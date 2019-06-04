//import liraries
import React, { Component } from 'react';
import { Modal, Text,View, StyleSheet,Image } from 'react-native';
import {  createAppContainer } from 'react-navigation'; 
import {createBottomTabNavigator } from 'react-navigation-tabs';
import Create_products from './notes_components/Create_products';
import Create_contacts from './notes_components/Create_contacts';


const add_note = createBottomTabNavigator( 
	{
		Contacts: {
			screen: Create_contacts,
			navigationOptions: {
				tabBarIcon: ({ focused, tintColor }) => {
					const iconFocused = focused ? 'add-contact-active.png' : 'add-contact-inactive.png';
				 
					if(focused){
						return (     
							<View>
							   <Image  
								style ={{width:30,height:30}}
								source={require('../../assets/img/add-contact-active.png')}
								/>
								 
							 </View>
						);
					}else{
						return (     
							<View>
							   <Image  
								style ={{width:30,height:30}}
								source={require('../../assets/img/add-contact-inactive.png')}
								/>
								 
							 </View>
						);
					}
					
				}
			}
		},
        Products: {
			screen: Create_products,
			navigationOptions: { 
				tabBarIcon: ({ focused, tintColor }) => {
					const iconFocused = focused ? 'add-product-active.png' : 'add-product-inactive.png';
					if(focused){

						return (
							<View> 
								 <Image 
								style ={{width:30,height:30}}
								source={require('../../assets/img/add-product-active.png')}
								/>
							</View>
						);
					}else{
						return (
							<View> 
								 <Image 
								style ={{width:30,height:30}}
								source={require('../../assets/img/add-product-inactive.png')}
								/>
							</View>
						);
					}
				
				}
			}
    } 
 
	},
	{
		initialRouteName:"Contacts",
	 
	},  
   
	{
		tabBarOptions: { 
			activeTintColor: 'red',
			inactiveTintColor: 'pink', 
			activeBackgroundColor:"red",
			labelStyle: {
				fontSize: 20,
			  },

			style: { 
				paddingVertical: 0,
				height: 100 , 
				borderTopWidth: 1,
				borderTopColor: 'red'
			}
		}
	}
);
 
const Note_tab =  createAppContainer(add_note);

// create a component
class Add_contact_products extends Component {
   
   


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
							this.setModalVisible(false);
						}}>
							<Note_tab event_id={this.props.selected_event_id} /> 
						
					</Modal>
			</View>
           
        );
    }
}

// define your styles
const styles = StyleSheet.create({
    container: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: '#2c3e50',
    },
});

//make this component available to the app
export default Add_contact_products;
