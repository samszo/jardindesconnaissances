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
	import com.degrafa.GraphicPointEX;
	
	/**
 	*  The GraphicPointEXCollection stores a collection of GraphicPointEX objects
 	**/
	public class GraphicPointEXCollection extends DegrafaCollection{
		/**
	 	* Constructor.
	 	*  
	 	* <p>The extended graphic point collection constructor accepts 2 optional arguments 
	 	* that specify the graphic points to be added and a event operation flag.</p>
	 	* 
	 	* @param array An array of GraphicPointEX objects.
	 	* @param suppressEvents A boolean value indicating if events should not be 
	 	* dispatched for this collection.
	 	*/
		public function GraphicPointEXCollection(array:Array=null,suppressEvents:Boolean=false){
			super(GraphicPointEX,array,suppressEvents);
		}
		
		/**
		* Adds a GraphicPointEX item to the collection.  
		* 
		* @param value The GraphicPointEX object to be added.
		* @return The GraphicPointEX object that was added.   
		**/			
		public function addItem(value:GraphicPointEX):GraphicPointEX{
			return super._addItem(value);
		}
		
		/**
		* Removes an GraphicPointEX item from the collection.  
		* 
		* @param value The GraphicPointEX object to be removed.
		* @return The GraphicPointEX object that was removed.   
		**/
		public function removeItem(value:GraphicPointEX):GraphicPointEX{
			return super._removeItem(value);
		}
		
		/**
		* Retrieve a GraphicPointEX item from the collection based on the index value 
		* requested.  
		* 
		* @param index The collection index of the GraphicPointEX object to retrieve.
		* @return The GraphicPointEX object that was requested if it exists.   
		**/
		public function getItemAt(index:Number):GraphicPointEX{
			return super._getItemAt(index);
		}
		
		/**
		* Retrieve a GraphicPointEX item from the collection based on the object value.  
		* 
		* @param value The GraphicPointEX object for which the index is to be retrieved.
		* @return The GraphicPointEX index value that was requested if it exists. 
		**/
		public function getItemIndex(value:GraphicPointEX):int{
			return super._getItemIndex(value);
		}
		
		/**
		* Adds a GraphicPointEX item to this collection at the specified index.  
		* 
		* @param value The GraphicPointEX object that is to be added.
		* @param index The position in the collection at which to add the GraphicPointEX object.
		* 
		* @return The GraphicPointEX object that was added.   
		**/
		public function addItemAt(value:GraphicPointEX,index:Number):GraphicPointEX{
			return super._addItemAt(value,index);	
		}
		
		/**
		* Removes a GraphicPointEX object from this collection at the specified index.  
		* 
		* @param index The index of the GraphicPointEX object to remove.
		* @return The GraphicPointEX object that was removed.   
		**/
		public function removeItemAt(index:Number):GraphicPointEX{
			return super._removeItemAt(index);;
		}
		
		/**
		* Change the index of the GraphicPointEX object within this collection.  
		* 
		* @param value The GraphicPointEX object that is to be repositioned.
		* @param newIndex The position at which to place the GraphicPointEX object within the collection.
		* @return True if the operation is successful False if unsuccessful.   
		**/
		public function setItemIndex(value:GraphicPointEX,newIndex:Number):Boolean{
			return super._setItemIndex(value,newIndex);
		}
		
		/**
		* Adds a series of GraphicPointEX objects to this collection.  
		*
		* @param value The collection to be added to this GraphicPointEX collection.  
		* @return The resulting GraphicPointEXCollection after the objects are added.   
		**/
		public function addItems(value:GraphicPointEXCollection):GraphicPointEXCollection{
			//todo
			super.concat(value.items)
			return this;
		}
		
		
	}
}