//import liraries
import React, { Component } from 'react';
import { Image,View, Text, StyleSheet,TextInput,TouchableOpacity,StatusBar } from 'react-native';
import AsyncStorage from '@react-native-community/async-storage';
 

// create a component
class Login extends Component {

    constructor(props){
        super(props);

        this.ifloginalerady();
    }

    componentDidMount() {
        StatusBar.setHidden(true);
     }

    state = { 
        username: '',
        password: '' ,
        isLoggedin: false,
        LoginStatus: '', 
      };

      ifloginalerady = async()=>{
          try {
           let  token  =  await AsyncStorage.getItem('cookies');
           let data  = JSON.parse(token);
             if(data.logged_in){
                this.props.navigation.navigate('Home');
             }else{
                this.props.navigation.navigate('Login');
             }

          } catch (error) {
              
          }
      }

      storeData = async (data) => {
      
        try {
          await AsyncStorage.setItem('cookies', JSON.stringify(data));
          this.props.navigation.navigate('Home');
        } catch (e) {
            this.props.navigation.navigate('Login');
        }
      }

 

    onLoginPressed = () => { 
        
        // alert(this.state.username);
         let username = this.state.username;
         let password = this.state.password;
        
       
         fetch('http://192.168.1.250/Api/login_now', {
             method: 'POST',
             headers: {
                 Accept: 'application/json',
                 'Content-Type': 'application/json',
             }, 
             body: JSON.stringify({
                 employeeid:username,
                 password:password,
             }),
             }).then((response) => response.json())
             .then((responseJson) => {
                  if (responseJson.result){

                    console.log(responseJson);
                  
                    this.storeData(responseJson.cookie_data);

                  }
                
               
             })
             .catch((error) => {
              // console.error(error);
             });
         }



    render() {
        return (
            
           
                
                <View style={styles.loginpage}> 
                        <Image 
                            style ={{width:200,height:200}}
                             source={require('../assets/img/logo.png')}
                        />

                        <Text style={{color:"red"}}>{this.state.LoginStatus}</Text>
                        <TextInput  
                        style={styles.inputfields}
                        // onChangeText={(text) => this.setState({text})} 
                        autoCapitalize="none" 
                        onSubmitEditing={() => this.passwordInput.focus()} 
                        autoCorrect={false} 
                        ref={(ref)=> this.username = ref} 
                        onChangeText={(text) => this.setState({username:text})}
                        keyboardType='email-address' 
                        returnKeyType="next" 
                        onSubmitEditing={this._submit}
                        textAlign="center"
                        placeholder='Username' 
                        placeholderTextColor='#818181'
                        />

                        <TextInput 
                        style={styles.inputfields}
                        // onChangeText={(text) => this.setState({text})}
                        returnKeyType="go" 
                        ref={(ref)=> this.password = ref} 
                        onChangeText={(text) => this.setState({password:text})}
                        placeholder='Password' 
                        textAlign="center"
                        placeholderTextColor='#818181' 
                        secureTextEntry
                        />

                        <View style={{alignItems:"center"}}>
                            <TouchableOpacity onPress = {() => this.onLoginPressed()}  style={styles.buttons}>
                            <Text   style={styles.login}>LOGIN</Text>
                            </TouchableOpacity>
 

                        </View>
                    </View>
             

            
        );
    }
}

// define your styles
// define your styles
const styles = StyleSheet.create({
 
    inputfields:{
        opacity:0.4,
        marginBottom: 15,
        fontSize:22,
        backgroundColor:"#fff",  
        maxHeight: 50,
        minWidth: 300,  
        maxWidth: 300,
        borderColor: "gray",
        borderWidth: 1,
        paddingLeft:15
    },
    login:{
        backgroundColor:"#fff200",  
        alignItems:"center",
        alignContent: 'center',
        alignSelf: 'center',
        color:"#282525",
        fontSize: 16,
        minWidth:100, 
        paddingHorizontal: 50, 
        paddingTop: 10,
        paddingBottom: 10,
        marginBottom:20 ,
      
    },
    signup:{
        backgroundColor:"green", 
        color:"white",
        alignItems:"center",
        alignContent: 'center',
        minWidth:100, 
        alignSelf: 'center',
        fontSize: 16,
        paddingHorizontal: 50, 
        paddingTop: 10,
        paddingBottom: 10,
        marginBottom:15  ,
       
    },
    buttons:{ 
        alignItems:"center",
        alignContent: 'center',
        alignSelf: 'center', 
        minWidth:200, 
    },
   loginpage:{
        backgroundColor:"#176fc1", 
        paddingTop:130,
        alignItems:"center",
        flex:1,
        borderTopColor: '#fff200',
        borderTopWidth: 25,
   },
    picker:{
        borderColor:"gray",
        borderWidth:1
    }


});

//make this component available to the app
export default Login;
