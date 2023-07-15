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
	import com.degrafa.transform.ITransform;
	
	/**
 	*  The TransformCollection stores a collection of ITransform objects
 	**/
	public class TransformCollection extends DegrafaCollection{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The transform collection constructor accepts 2 optional arguments 
	 	* that specify the transforms to be added and a event operation flag.</p>
	 	* 
	 	* @param array An array of ITransform objects.
	 	* @param suppressEvents A boolean value indicating if events should be 
	 	* dispatched for this collection.
	 	*/
		public function TransformCollection(array:Array=null,suppressEvents:Boolean=false){
			super(ITransform,array,suppressEvents);
		}
		
		/**
		* Adds a ITransform item to the collection.  
		* 
		* @param value The ITransform object to be added.
		* @return The ITransform object that was added.   
		**/			
		public function addItem(value:ITransform):ITransform{
			return super._addItem(value);
		}
		
		/**
		* Removes an ITransform item from the collection.  
		* 
		* @param value The ITransform object to be removed.
		* @return The ITransform object that was removed.   
		**/
		public function removeItem(value:ITransform):ITransform{
			return super._removeItem(value);
		}
		
		/**
		* Retrieve a ITransform item from the collection based on the index value 
		* requested.  
		* 
		* @param index The collection index of the ITransform object to retrieve.
		* @return The ITransform object that was requested if it exists.   
		**/
		public function getItemAt(index:Number):ITransform{
			return super._getItemAt(index);
		}
		
		/**
		* Adds a ITransform item to this collection at the specified index.  
		* 
		* @param value The ITransform object that is to be added.
		* @param index The position in the collection at which to add the ITransform object.
		* 
		* @return The ITransform object that was added.   
		**/
		public function getItemIndex(value:ITransform):int{
			return super._getItemIndex(value);
		}
		
		/**
		* Adds a ITransform item to this collection at the specified index.  
		* 
		* @param value The ITransform object that is to be added.
		* @param index The position in the collection at which to add the ITransform object.
		* 
		* @return The ITransform object that was added.   
		**/
		public function addItemAt(value:ITransform,index:Number):ITransform{
			return super._addItemAt(value,index);	
		}
		
		/**
		* Removes a ITransform object from this collection at the specified index.  
		* 
		* @param index The index of the ITransform object to remove.
		* @return The ITransform object that was removed.   
		**/
		public function removeItemAt(index:Number):ITransform{
			return super._removeItemAt(index);;
		}
		
		/**
		* Change the index of the ITransform object within this collection.  
		* 
		* @param value The ITransform object that is to be repositioned.
		* @param newIndex The position at which to place the ITransform object within the collection.
		* @return True if the operation is successful False if unsuccessful.   
		**/
		public function setItemIndex(value:ITransform,newIndex:Number):Boolean{
			return super._setItemIndex(value,newIndex);
		}
		
		/**
		* Adds a series of ITransform objects to this collection.  
		*
		* @param value The collection to be added to this ITransform collection.  
		* @return The resulting TransformCollection after the objects are added.   
		**/
		public function addItems(value:TransformCollection):TransformCollection{
			//todo
			super.concat(value.items)
			return this;
		}
		
		
	}
}