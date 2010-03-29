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
	
	import com.degrafa.core.IGraphicsFill;
	
	/**
 	*  The FillCollection stores a collection of IGraphicsFill objects
 	**/
	public class FillCollection extends DegrafaCollection{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The fill collection constructor accepts 2 optional arguments 
	 	* that specify the fills to be added and a event operation flag.</p>
	 	* 
	 	* @param array An array of IGraphicsFill objects.
	 	* @param suppressEvents A boolean value indicating if events should not be 
	 	* dispatched for this collection.
	 	*/		
		public function FillCollection(array:Array=null,suppressEvents:Boolean=false){
			super(IGraphicsFill,array,suppressEvents);
		}
		
		/**
		* Adds a IGraphicsFill item to the collection.  
		* 
		* @param value The IGraphicsFill object to be added.
		* @return The IGraphicsFill object that was added.   
		**/		
		public function addItem(value:IGraphicsFill):IGraphicsFill{
			return super._addItem(value);
		}
		
		/**
		* Removes an IGraphicsFill item from the collection.  
		* 
		* @param value The IGraphicsFill object to be removed.
		* @return The IGraphicsFill object that was removed.   
		**/	
		public function removeItem(value:IGraphicsFill):IGraphicsFill{
			return super._removeItem(value);
		}
		
		/**
		* Retrieve a IGraphicsFill item from the collection based on the index value 
		* requested.  
		* 
		* @param index The collection index of the IGraphicsFill object to retrieve.
		* @return The IGraphicsFill object that was requested if it exists.   
		**/
		public function getItemAt(index:Number):IGraphicsFill{
			return super._getItemAt(index);
		}
		
		/**
		* Retrieve a IGraphicsFill item from the collection based on the object value.  
		* 
		* @param value The IGraphicsFill object for which the index is to be retrieved.
		* @return The IGraphicsFill index value that was requested if it exists. 
		**/
		public function getItemIndex(value:IGraphicsFill):int{
			return super._getItemIndex(value);
		}
		
		/**
		* Adds a IGraphicsFill item to this collection at the specified index.  
		* 
		* @param value The IGraphicsFill object that is to be added.
		* @param index The position in the collection at which to add the IGraphicsFill object.
		* 
		* @return The IGraphicsFill object that was added.   
		**/
		public function addItemAt(value:IGraphicsFill,index:Number):IGraphicsFill{
			return super._addItemAt(value,index);	
		}
		
		/**
		* Removes a IGraphicsFill object from this collection at the specified index.  
		* 
		* @param index The index of the IGraphicsFill object to remove.
		* @return The IGraphicsFill object that was removed.   
		**/
		public function removeItemAt(index:Number):IGraphicsFill{
			return super._removeItemAt(index);;
		}
		
		/**
		* Change the index of the IGraphicsFill object within this collection.  
		* 
		* @param value The IGraphicsFill object that is to be repositioned.
		* @param newIndex The position at which to place the IGraphicsFill object within the collection.
		* @return True if the operation is successful False if unsuccessful.   
		**/
		public function setItemIndex(value:IGraphicsFill,newIndex:Number):Boolean{
			return super._setItemIndex(value,newIndex);
		}
		
		/**
		* Adds a series of IGraphicsFill objects to this collection.  
		*
		* @param value The collection to be added to this IGraphicsFill collection.  
		* @return The resulting FillCollection after the objects are added.   
		**/
		public function addItems(value:FillCollection):FillCollection{
			//todo
			super.concat(value.items)
			return this;
		}
		
		
	}
}