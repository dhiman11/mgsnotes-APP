//import liraries
import React, { Component } from 'react';
import { View, Text, StyleSheet,FlatList,RefreshControl,ActivityIndicator } from 'react-native';
import Event_compo from './History_components/Event_compo.js';
import Contact_compo from './History_components/Contact_compo.js';
import Product_compo from './History_components/Product_compo.js';
import Supplier_compo from './History_components/Supplier_compo.js';
 
 
// create a component
class History_page extends Component {

    constructor(props){
        super(props);

        this.state={
            loadmore_test:'',
            isRefreshing:false,
            loadingmore:true, 
            history_array:[],
            limit:0,
        
        } 
        this.__load_historydata(this.state.limit); 
     
    }


    /////////////////////////////////////////////////////
    ////////////// Reload Data when Change Tab //////////

    componentDidMount() {
        this.didFocusListener = this.props.navigation.addListener(
          'didFocus',
          () => {
            this.setState({loadingmore:true});
            this.setState({history_array:[]});
            this.setState({limit:0});
            this.__load_historydata(0); 

             },
        );
      }

      componentWillUnmount() {
        this.didFocusListener.remove();
      }

    /////////////////////////////////////////////////////
    ////////////// Reload Data when Change Tab //////////



    ////// ON REFERESH PAGE /////////
    _onRefresh=()=>{
        this.setState({isRefreshing:true})
        this.setState({history_array:[]})
        this.setState({limit:0})
        this.__load_historydata(0); 
        this.setState({isRefreshing:false})
        this.setState({ loadmore_test:' ',}) ;
    }

 
    ///// LOAD MORE ////////////////////
    handleLoadMore = () => {
        
          this.setState({ loadmore_test:'Loading...',}) ;
          var limit = parseInt(this.state.limit) + parseInt(20);  // increase page by 1
          this.setState({limit:limit});
          this.__load_historydata(limit);  // method for API call 
          this.setState({ loadmore_test:'Scroll down to load more',}) ;
       
      };


    ////////////////////////////// LOAD MORE DATA ///////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////// 
    __load_historydata=(limit)=>{ 
       ///++++++++++++++++++++++++++++++++++++++++++++++++++++++++++//// 
        ///++++++++++++++++++++++++++++++++++++++++++++++++++++++++++////

        fetch('http://192.168.1.250/Api/history/History_page/history_data', { 
            method: 'POST', 
            headers: { 
                 Accept: 'application/json',
                'Content-Type': 'application/json',
            }, 
            body: JSON.stringify({
                limit:limit, 
             }),
            }).then((response) => response.json())
            .then((responseJson) => {
           
                this.setState({
                    history_array:[...this.state.history_array,...responseJson],
                    loadingmore:false
                }); 
  
            })
            .catch((error) => {
              console.error(error);
            });

        ///++++++++++++++++++++++++++++++++++++++++++++++++++++++++++////
        ///++++++++++++++++++++++++++++++++++++++++++++++++++++++++++////
 

        }

        ////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////
 


    render() {
        return (
            <View style={styles.container}>
                <View>
                     <Text style={styles.heading}>HISTORY</Text> 
                </View>
                {this.state.loadingmore && 
                 <ActivityIndicator animating={this.state.loadingmore} size="large" color="red" /> 
                }
                <FlatList 
                    numColumns={1} 
                    data={this.state.history_array} 
                    refreshControl={
                        <RefreshControl
                          refreshing={this.state.isRefreshing}
                          onRefresh={this._onRefresh.bind(this)}
                        />
                      }
                    keyExtractor={(item, index) => index.toString()}  
                    onEndReachedThreshold={3}  
                    onEndReached={this.handleLoadMore.bind(this)}
                    renderItem={({item , index }) => (


                         
                        <View>
                             
                            {item.data_type =='event' &&
                                <Event_compo  images  = {item.event_images} data ={item.event}  />   
                            }
                         
                        
                             {item.data_type =='supplier' &&
                               <Supplier_compo  images  = {item.supplier_images} data ={item.data}  />
                            }  

                                 {item.data_type =='contact' &&
                                <Contact_compo data ={item.data}  images ={item.contact_images}  />   
                              }  

                                {item.data_type =='product' &&
                                    <Product_compo  images ={item.pro_images} data={item.data} /> 
                                  }   
                             
                        </View>
                   
                        )} 
                />
         
                <View>
                   <Text>{this.state.loadmore_test}</Text> 
                </View>
            
 
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
        backgroundColor: '#fff',
        
        
    },
    heading:{
        fontSize:22,
        color:"#000000",
        marginTop: 10, 
        width:100,
        marginBottom: 20,
        fontWeight:"bold"
    }
});

//make this component available to the app
export default History_page; 
