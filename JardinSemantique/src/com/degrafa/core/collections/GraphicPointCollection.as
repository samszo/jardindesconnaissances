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
	import com.degrafa.core.IDegrafaObject;
	import com.degrafa.GraphicPointEX;
	import com.degrafa.IGraphicPoint;
	
	/**
 	*  The GraphicPointCollection stores a collection of IGraphicPoint objects
 	**/	
	public class GraphicPointCollection extends DegrafaCollection{
		/**
	 	* Constructor.
	 	*  
	 	* <p>The graphic point collection constructor accepts 2 optional arguments 
	 	* that specify the graphic points to be added and a event operation flag.</p>
	 	* 
	 	* @param array An array of IGraphicPoint objects.
	 	* @param suppressEvents A boolean value indicating if events should not be 
	 	* dispatched for this collection.
	 	*/	
		public function GraphicPointCollection(array:Array=null,suppressEvents:Boolean=false){
			super(IGraphicPoint,array,suppressEvents);
		}
		
		/**
		* Adds a IGraphicPoint item to the collection.  
		* 
		* @param value The IGraphicPoint object to be added.
		* @return The IGraphicPoint object that was added.   
		**/			
		public function addItem(value:IGraphicPoint):IGraphicPoint{
			return super._addItem(value);
		}
		
		/**
		* Removes an IGraphicPoint item from the collection.  
		* 
		* @param value The IGraphicPoint object to be removed.
		* @return The IGraphicPoint object that was removed.   
		**/	
		public function removeItem(value:IGraphicPoint):IGraphicPoint{
			return super._removeItem(value);
		}
		
		/**
		* Retrieve a IGraphicPoint item from the collection based on the index value 
		* requested.  
		* 
		* @param index The collection index of the IGraphicPoint object to retrieve.
		* @return The IGraphicPoint object that was requested if it exists.   
		**/
		public function getItemAt(index:Number):IGraphicPoint{
			return super._getItemAt(index);
		}
		
		/**
		* Retrieve a IGraphicPoint item from the collection based on the object value.  
		* 
		* @param value The IGraphicPoint object for which the index is to be retrieved.
		* @return The IGraphicPoint index value that was requested if it exists. 
		**/
		public function getItemIndex(value:IGraphicPoint):int{
			return super._getItemIndex(value);
		}
		
		/**
		* Adds a IGraphicPoint item to this collection at the specified index.  
		* 
		* @param value The IGraphicPoint object that is to be added.
		* @param index The position in the collection at which to add the IGraphicPoint object.
		* 
		* @return The IGraphicPoint object that was added.   
		**/
		public function addItemAt(value:IGraphicPoint,index:Number):IGraphicPoint{
			return super._addItemAt(value,index);	
		}
		
		/**
		* Removes a IGraphicPoint object from this collection at the specified index.  
		* 
		* @param index The index of the IGraphicPoint object to remove.
		* @return The IGraphicPoint object that was removed.   
		**/
		public function removeItemAt(index:Number):IGraphicPoint{
			return super._removeItemAt(index);;
		}
		
		/**
		* Change the index of the IGraphicPoint object within this collection.  
		* 
		* @param value The IGraphicPoint object that is to be repositioned.
		* @param newIndex The position at which to place the IGraphicPoint object within the collection.
		* @return True if the operation is successful False if unsuccessful.   
		**/
		public function setItemIndex(value:IGraphicPoint,newIndex:Number):Boolean{
			return super._setItemIndex(value,newIndex);
		}
		
		/**
		* Adds a series of IGraphicPoint objects to this collection.  
		*
		* @param value The collection to be added to this IGraphicPoint collection.  
		* @return The resulting GraphicPointCollection after the objects are added.   
		**/
		public function addItems(value:GraphicPointCollection):GraphicPointCollection{
			//todo
			super.concat(value.items)
			return this;
		}
		
		
	}
}