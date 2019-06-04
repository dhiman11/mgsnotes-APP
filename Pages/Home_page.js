//import liraries
import React from 'react';
import { View, Text, StyleSheet,Image } from 'react-native';
import AsyncStorage from '@react-native-community/async-storage';
import { createAppContainer } from 'react-navigation';
import {createBottomTabNavigator } from 'react-navigation-tabs';
// create a component
import History_page from './History_page'; 
import Add_note from './Add_note';
import Product_page from './Product_page';



const Appli = createBottomTabNavigator(
	{
		Products: {
			screen: Product_page,
			navigationOptions: {
				tabBarIcon: ({ focused, tintColor }) => {
					const iconFocused = focused ? '#7444C0' : '#363636';
					return (  
                        <View><Image 
                        style ={{width:25,height:25}}
                        source={require('../assets/img/search.png')}
                         /></View>
					);
				}
			}
		},
        Add: {
			screen: Add_note,
			navigationOptions: {
       
				tabBarIcon: ({ focused, tintColor }) => {
					const iconFocused = focused ? '#7444C0' : '#363636';
					return (
                        <View><Image 
                        style ={{width:25,height:25}}
                        source={require('../assets/img/add.png')}
                         /></View>
					);
				}
			}
    },
    History: {
			screen: History_page,
			navigationOptions: {
				tabBarIcon: ({ focused, tintColor }) => {
					const iconFocused = focused ? '#7444C0' : '#363636';
					return (
                        <View><Image 
                        style ={{width:25,height:25}}
                        source={require('../assets/img/history.png')}
                         /></View>
					);
				}
			}
		} 
 
	},
	{
		initialRouteName:"History"
	},
	{
		tabBarOptions: {
           
			activeTintColor: 'red',
			inactiveTintColor: 'pink',
		 
			style: {
				backgroundColor: 'blue',
				borderTopWidth: 0,
				paddingVertical: 0,
				height: 70,
				marginBottom: 0,
				shadowOpacity: 0.05,
				shadowRadius: 0,
				shadowColor: '#000',
				shadowOffset: { height: 0, width: 0 }
			}
		}
	}
);

const styles = StyleSheet.create({
	tabButton: {
		paddingTop: 0,
		paddingBottom: 0,
		alignItems: 'center',
		justifyContent: 'center',
		flex: 1
	},
	tabButtonText: {
		textTransform: 'uppercase'
	},
	icon: {
		fontFamily: 'tinderclone',
		height: 30, 
		fontSize:25,
		paddingBottom: 0
	}
});





const Mainmenu =  createAppContainer(Appli);



export default class Home_page extends React.Component {
	///////////////////////////
	///////////////////////////
	constructor(props) {
		super(props);
 
		this.state={
			logged_in:false
		}  
		this._bootstrapAsync(props);
	  }


	  _bootstrapAsync = async (props) => { 
		const userToken = await AsyncStorage.getItem('cookies'); 
		try {
				token = JSON.parse(userToken);  
				//////////////////////////////
				this.setState({ 
					logged_in :  token.logged_in 
				});

		} catch (error) { 
			
            
		} 
		// This will switch to the App screen or Auth screen and this loading
		// screen will be unmounted and thrown away.
		 
	  };
	  
 
	///////////////////////////
	///////////////////////////
	render() { 
	 
			return (<Mainmenu/>); 
		
	}
	///////////////////////////
	///////////////////////////

  }
 