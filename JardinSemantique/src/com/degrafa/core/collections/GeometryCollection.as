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
	import com.degrafa.IGeometry;
	
	/**
 	*  The GeometryCollection stores a collection of IGeometry objects
 	**/
	public class GeometryCollection extends DegrafaCollection{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The geometry collection constructor accepts 2 optional arguments 
	 	* that specify the geometry objects to be added and a event operation flag.</p>
	 	* 
	 	* @param array An array of IGeometry objects.
	 	* @param suppressEvents A boolean value indicating if events should not be 
	 	* dispatched for this collection.
	 	*/	
		public function GeometryCollection(array:Array=null,suppressEvents:Boolean=false){
			super(IGeometry,array,suppressEvents);
		}
		
		/**
		* Adds a IGeometry item to the collection.  
		* 
		* @param value The IGeometry object to be added.
		* @return The IGeometry object that was added.   
		**/		
		public function addItem(value:IGeometry):IGeometry{
			return super._addItem(value);
		}
		
		/**
		* Removes an IGeometry item from the collection.  
		* 
		* @param value The IGeometry object to be removed.
		* @return The IGeometry object that was removed.   
		**/
		public function removeItem(value:IGeometry):IGeometry{
			return super._removeItem(value);
		}
		
		/**
		* Retrieve a IGeometry item from the collection based on the index value 
		* requested.  
		* 
		* @param index The collection index of the IGeometry object to retrieve.
		* @return The IGeometry object that was requested if it exists.   
		**/
		public function getItemAt(index:Number):IGeometry{
			return super._getItemAt(index);
		}
		
		/**
		* Retrieve a IGeometry item from the collection based on the object value.  
		* 
		* @param value The IGeometry object for which the index is to be retrieved.
		* @return The IGeometry index value that was requested if it exists. 
		**/
		public function getItemIndex(value:IGeometry):int{
			return super._getItemIndex(value);
		}
		
		/**
		* Adds a IGeometry item to this collection at the specified index.  
		* 
		* @param value The IGeometry object that is to be added.
		* @param index The position in the collection at which to add the IGeometry object.
		* 
		* @return The IGeometry object that was added.   
		**/
		public function addItemAt(value:IGeometry,index:Number):IGeometry{
			return super._addItemAt(value,index);	
		}
		
		/**
		* Removes a IGeometry object from this collection at the specified index.  
		* 
		* @param index The index of the IGeometry object to remove.
		* @return The IGeometry object that was removed.   
		**/
		public function removeItemAt(index:Number):IGeometry{
			return super._removeItemAt(index);;
		}
		
		/**
		* Change the index of the IGeometry object within this collection.  
		* 
		* @param value The IGeometry object that is to be repositioned.
		* @param newIndex The position at which to place the IGeometry object within the collection.
		* @return True if the operation is successful False if unsuccessful.   
		**/
		public function setItemIndex(value:IGeometry,newIndex:Number):Boolean{
			return super._setItemIndex(value,newIndex);
		}
		
		/**
		* Adds a series of IGeometry objects to this collection.  
		*
		* @param value The collection to be added to this IGeometry collection.  
		* @return The resulting GeometryCollection after the objects are added.   
		**/
		public function addItems(value:GeometryCollection):GeometryCollection{
			//todo
			super.concat(value.items)
			return this;
		}
		
	}
}