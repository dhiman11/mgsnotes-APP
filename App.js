import React, { Component } from 'react';
import {createStackNavigator, createAppContainer } from 'react-navigation';
import firebase from 'react-native-firebase';
import type, { Notification, NotificationOpen } from 'react-native-firebase';

import Login from './Pages/Login';
import Home_page from './Pages/Home_page';
 

 
 




 
const MainNavigator = createStackNavigator({
	Login: {screen: Login},
	Home: {screen: Home_page},

  },
  {
	headerMode: 'none',
});

 
const Appcontainer =  createAppContainer(MainNavigator);

class App extends Component {


	////////////////////////////////////////
	////////////////////////////////////////
	////////  NOTIFICATION START ///////////
	////////////////////////////////////////

	subscribeToNotificationListeners() {
        const channel = new firebase.notifications.Android.Channel(
            'MGS Notes Shanghai', // To be Replaced as per use
            'Notifications', // To be Replaced as per use
            firebase.notifications.Android.Importance.Max
        ).setDescription('Your App To manage notes .');
        firebase.notifications().android.createChannel(channel);
        
        this.notificationListener = firebase.notifications().onNotification((notification) => {
            console.log('onNotification notification-->', notification);
            console.log('onNotification notification.data -->', notification.data);
            console.log('onNotification notification.notification -->', notification.notification);
            // Process your notification as required
            this.displayNotification(notification)
        });
	}



	displayNotification = (notification) => {
        if (Platform.OS === 'android') {
            const localNotification = new firebase.notifications.Notification({
                sound: 'default',
                show_in_foreground: true,
            }).setNotificationId(notification.notificationId)
                .setTitle(notification.title)
                .setSubtitle(notification.subtitle)
                .setBody(notification.body)
                .setData(notification.data)
                .android.setChannelId('notification_channel_name') // e.g. the id you chose above
                .android.setSmallIcon('ic_notification_icon') // create this icon in Android Studio
                .android.setColor(colors.colorAccent) // you can set a color here
                .android.setPriority(firebase.notifications.Android.Priority.High);
 
            firebase.notifications()
                .displayNotification(localNotification)
                .catch(err => console.error(err));
 
        }
	}
	

	componentDidMount() {
        firebase.messaging().hasPermission().then(hasPermission => {
            if (hasPermission) {
                this.subscribeToNotificationListeners()
            } else {
                firebase.messaging().requestPermission().then(() => {
                    this.subscribeToNotificationListeners()
                }).catch(error => {
                    console.error(error);
 
                })
            }
        })
    }
 
    componentWillUnmount() {
        this.notificationListener();
	}
	

	////////////////////////////////////////
	////////////////////////////////////////
	////////  NOTIFICATION END ///////////
	////////////////////////////////////////


	


	render() {
		return (<Appcontainer/>);
	}

  }


  export default App;



 


 