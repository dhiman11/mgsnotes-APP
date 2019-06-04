//import liraries
import React, { Component } from 'react';
import { View, Text, StyleSheet,TouchableOpacity } from 'react-native';
 

// create a component
class Last_supplier extends Component {

    set_supplier_note_id=(supplier_id)=>{

        this.props.update_supp_id(supplier_id);

    }

    render() {
        return (
            <View style={styles.container}>
                <TouchableOpacity 	onPress={() => { this.set_supplier_note_id(this.props); }}>
                    <View  style={styles.supplierlist}> 
                            <Text style={{color:"#fff"}}>{this.props.suppliername}</Text> 
                    </View>
                </TouchableOpacity>
            </View>
        );
    }
}

// define your styles
const styles = StyleSheet.create({
    container:{
        flex:1
    },
    supplierlist:{
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight:10,
        backgroundColor: '#176fc1',
        padding:10 
    }
});

//make this component available to the app
export default Last_supplier;
