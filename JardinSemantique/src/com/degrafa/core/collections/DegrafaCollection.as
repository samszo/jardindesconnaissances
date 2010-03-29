////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2008 Jason Hawryluk, Juan Sanchez, Andy McIntosh, Ben Stucki 
// and Pavan Podila.
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
////////////////////////////////////////////////////////////////////////////////
package com.degrafa.core.collections{
	
	import com.degrafa.core.DegrafaObject; 
	import com.degrafa.core.IDegrafaObject;
	
	import flash.utils.getQualifiedClassName;
	
	import mx.events.PropertyChangeEvent;
	
	//base degrafa collection that proxies the array type
	[DefaultProperty("items")]
	/**
 	*  The Degrafa collection stores a collection of objects 
 	*  of a specific type and is the base class for all collection
 	*  objects.
 	**/
	public class DegrafaCollection extends DegrafaObject
	{
		/**
 		*  The type of objects being stored in this colelction.
 		**/
		private var type:Class;
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The Degrafa collection constructor accepts 4 optional arguments 
	 	* that specify it's type, an array of values of the expected type to be added 
	 	* and 2 event operation flags.</p>
	 	* 
	 	* @param type An class value specifing the types of objects being stored here.
	 	* @param array An array of objects that are of the specified type.
	 	* @param suppressEvents A boolean value indicating if events should not be 
	 	* dispatched for this collection.
	 	* @param enableTypeChecking A boolean value indicating if type checking should be performed.
	 	*/							
		public function DegrafaCollection(type:Class,array:Array=null,suppressEvents:Boolean=false,enableTypeChecking:Boolean=true){
	       	
	       	this.type = type;
	       	_enableTypeChecking = enableTypeChecking;
	       	
	       	suppressEventProcessing = suppressEvents;
	       	 
	       	if(array){
	       		items =array;
	       	}
	       	
	    }
				
		private var _enableTypeChecking:Boolean=true;
		/**
		* Allows internal type checking to be turned off.
		**/
		public function get enableTypeChecking():Boolean{
			return _enableTypeChecking;
		}
		public function set enableTypeChecking(value:Boolean):void{
			_enableTypeChecking=value;
		}
		
		
		private var _items:Array=[];
		/**
		* An array of items being stored in this collection.
		**/
		public function get items():Array{
			return _items;
		}
		public function set items(value:Array):void{
			
			//type check and throw excemption is invalide type found
			if(_enableTypeChecking){
				for each (var item:Object in value){
					if(!item is type){
						throw new TypeError(flash.utils.getQualifiedClassName(item) + 
						" is not a valid " + 
						flash.utils.getQualifiedClassName(type));
						
						return;		
					}
				}
			}
			
			//compare and update
			if(value !=_items){
				var oldValue:Array = _items;
				_items=value;
				
				if(enableEvents && hasEventManager){
					//call local helper to dispatch event	
					initChange("items",oldValue,_items,this);
				}
			}
			
			//add event listeners
			addListeners();
			
		}
		
		
		/**
		* Adds a new item to the collection.
		* 
		* @param value The item to add. 
		* @return The item added. 
		**/
		protected function _addItem(value:*):*{
			
			items=items.concat(value);
			return value;
		}
		
		/**
		* Removes a item from the collection.
		*
		* @param value The item to remove.
		* @return The item removed. 
		**/
		protected function _removeItem(value:*):*{
			
			//get the index
			var index:int = items.indexOf(value,0);
			_removeItemAt(index);
			
			return null;
		}
		
		/**
		* Return a item at the given index.
		*
		* @param value The item index to return.
		* @return The item requested. 
		**/
		protected function _getItemAt(index:Number):*{
			return items[index];
		}
		
		/**
		* Return the index for the item in the collection.
		*
		* @param value The item to find.
		* @return The index location of the item. 
		**/
		protected function _getItemIndex(value:*):int{
			return items.indexOf(value,0);
		}
		
		
		/**
		* Add a item at the given index.
		*
		* @param value The item to add to the collection.
		* @param index The index at which to add the item.
		* @return The item added.
		**/
		protected function _addItemAt(value:*,index:Number):*{
			items.splice(index,0,value);
			return value;
		}
		
		/**
		* Removes a item from the collection.
		*
		* @param value The item index to remove. 
		* @return The removed item.
		**/
		protected function _removeItemAt(index:Number):*{
			
				var oldValue:Array = _items;
				var newItem:Object = items.splice(index,0)[0];
				
				if(oldValue != items){
					//call local helper to dispatch event	
					initChange("items",oldValue,_items,this);
				}
							
			return newItem;
		}
		
		/**
		* Change the position of an item in the collection
		*
		* @param index The items new index. 
		* @param value The item to be repositioned.
		* @return Returns true is the item was repositioned. 
		**/
		protected function _setItemIndex(value:*,newIndex:Number):Boolean{
			
			return true;
		
		}
		
		//to be overidden in subclasse if nessesary
		
		/**
		* Addes a property change event listener to each item in the collection.
		**/
		public function addListeners():void{
			if(enableEvents){
				for each (var item:Object in items){
					if(item is IDegrafaObject){
						if(IDegrafaObject(item).enableEvents){
							IDegrafaObject(item).addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
						}
					}
					
				}
			}
		}
		
		/**
		* Removes the property change event listener from each item in the collection.
		**/
		public function removeListeners():void{
			for each (var item:Object in items){
				if(item is IDegrafaObject){
					IDegrafaObject(item).removeEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
				}
			}
		}
		
		/**
		* Property change event handler for this collection.
		**/
		public function propertyChangeHandler(event:PropertyChangeEvent):void{
			if(!suppressEventProcessing){
				dispatchEvent(event);
			}
		}
		
		
		//proxy for array calls in some cases the subclasses may override these
		//to provide additional function or safety
		
		/**
		* Concatenates the elements specified in the parameters with the elements in an array and creates a new array.
		* 
		* @see Array
		**/
		public function concat(... args):Array{
			return items.concat(args);
		} 
		
		/**
		* Executes a test function on each item in the array until an item is reached that returns false for the specified function.
		* 
		* @see Array
		**/
		public function every(callback:Function, thisObject:* = null):Boolean{
			return items.every(callback,thisObject);
		}
		
		/**
		* Executes a test function on each item in the array and constructs a new array for all items that return true for the specified function.
		* 
		* @see Array
		**/
		public function filter(callback:Function, thisObject:* = null):Array{
			return items.filter(callback,thisObject);
		}
		
		/**
		* Executes a function on each item in the array.
		* 
		* @see Array
		**/
		public function forEach(callback:Function, thisObject:* = null):void{
			items.forEach(callback, thisObject);
		}
		
		/**
		* Searches for an item in an array by using strict equality (===) and returns the index position of the item.
		* 
		* @see Array
		**/
		public function indexOf(searchElement:*, fromIndex:int = 0):int{
			return items.indexOf(searchElement, fromIndex);
		}
		
		/**
		* Converts the elements in an array to strings, inserts the specified separator between the elements, concatenates them, and returns the resulting string.
		* 
		* @see Array
		**/
		public function join(sep:*):String{
			return items.join(sep);
		}
		
		/**
		* Searches for an item in an array, working backward from the last item, and returns the index position of the matching item using strict equality (===).
		* 
		* @see Array
		**/
		public function lastIndexOf(searchElement:*, fromIndex:int = 0x7fffffff):int{
			return items.lastIndexOf(searchElement, fromIndex);
		}
		
		/**
		* Executes a function on each item in an array, and constructs a new array of items corresponding to the results of the function on each item in the original array.
		* 
		* @see Array
		**/
		public function map(callback:Function, thisObject:* = null):Array{
			return items.map(callback, thisObject);
		}
		
		/**
		* Removes the last element from an array and returns the value of that element.
		* 
		* @see Array
		**/
		public function pop():*{
			return items.pop();
		}
		
		/**
		* Adds one or more elements to the end of an array and returns the new length of the array.
		* 
		* @see Array
		**/
		public function push(... args):uint{
			return items.push(args);
		}
		
		/**
		* Reverses the array in place.
		* 
		* @see Array
		**/
		public function reverse():Array{
			return items.reverse();
		}
		
		/**
		* Removes the first element from an array and returns that element.
		* 
		* @see Array
		**/
		public function shift():*{
			return items.shift();
		}
		
		/**
		* Returns a new array that consists of a range of elements from the original array, without modifying the original array.
		* 
		* @see Array
		**/
		public function slice(startIndex:int = 0, endIndex:int = 16777215):Array{
			return items.slice(startIndex, endIndex);
		}
		
		/**
		* Executes a test function on each item in the array until an item is reached that returns true.
		* 
		* @see Array
		**/
		public function some(callback:Function, thisObject:* = null):Boolean{
			return items.some(callback, thisObject);
		}
		
		/**
		* Sorts the elements in an array.
		* 
		* @see Array
		**/
		public function sort(... args):Array{
			return items.sort(args);
		}
		
		/**
		* Sorts the elements in an array according to one or more fields in the array.
		* 
		* @see Array
		**/
		public function sortOn(fieldName:Object, options:Object = null):Array{
			return items.sortOn(fieldName,options);
		}
		
		/**
		* Adds elements to and removes elements from an array.
		* 
		* @see Array
		**/
		public function splice(startIndex:int, deleteCount:uint, ... values):Array{
			return items.splice(startIndex,deleteCount,values);
		}
		
		/**
		* Adds one or more elements to the beginning of an array and returns the new length of the array.
		* 
		* @see Array
		**/
		public function unshift(... args):uint{
			return items.unshift(args);
		}
		
	}
}