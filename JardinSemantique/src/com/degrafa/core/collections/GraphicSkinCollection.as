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
	
	//these subclasses are to make api access easier and to ensure 
	//the the correct types are returned.
	
	import com.degrafa.core.IGraphicSkin;
	
	/**
 	*  The GraphicSkinCollection stores a collection of IGraphicSkin objects
 	**/	
	public class GraphicSkinCollection extends DegrafaCollection{
		/**
	 	* Constructor.
	 	*  
	 	* <p>The graphic skin collection constructor accepts 2 optional arguments 
	 	* that specify the graphic skins to be added and a event operation flag.</p>
	 	* 
	 	* @param array An array of IGraphicSkin objects.
	 	* @param suppressEvents A boolean value indicating if events should not be 
	 	* dispatched for this collection.
	 	*/
		public function GraphicSkinCollection(array:Array=null,suppressEvents:Boolean=false){
			super(IGraphicSkin,array,suppressEvents);
		}
		
		/**
		* Adds a IGraphicSkin item to the collection.  
		* 
		* @param value The IGraphicSkin object to be added.
		* @return The IGraphicSkin object that was added.   
		**/			
		public function addItem(value:IGraphicSkin):IGraphicSkin{
			return super._addItem(value);
		}
		
		/**
		* Removes an IGraphicSkin item from the collection.  
		* 
		* @param value The IGraphicSkin object to be removed.
		* @return The IGraphicSkin object that was removed.   
		**/	
		public function removeItem(value:IGraphicSkin):IGraphicSkin{
			return super._removeItem(value);
		}
		
		/**
		* Retrieve a IGraphicSkin item from the collection based on the index value 
		* requested.  
		* 
		* @param index The collection index of the IGraphicSkin object to retrieve.
		* @return The IGraphicSkin object that was requested if it exists.   
		**/
		public function getItemAt(index:Number):IGraphicSkin{
			return super._getItemAt(index);
		}
		
		/**
		* Retrieve a IGraphicSkin item from the collection based on the object value.  
		* 
		* @param value The IGraphicSkin object for which the index is to be retrieved.
		* @return The IGraphicSkin index value that was requested if it exists. 
		**/
		public function getItemIndex(value:IGraphicSkin):int{
			return super._getItemIndex(value);
		}
		
		/**
		* Adds a IGraphicSkin item to this collection at the specified index.  
		* 
		* @param value The IGraphicSkin object that is to be added.
		* @param index The position in the collection at which to add the IGraphicSkin object.
		* 
		* @return The IGraphicSkin object that was added.   
		**/
		public function addItemAt(value:IGraphicSkin,index:Number):IGraphicSkin{
			return super._addItemAt(value,index);	
		}
		/**
		* Removes a IGraphicSkin object from this collection at the specified index.  
		* 
		* @param index The index of the IGraphicSkin object to remove.
		* @return The IGraphicSkin object that was removed.   
		**/
		public function removeItemAt(index:Number):IGraphicSkin{
			return super._removeItemAt(index);;
		}
		/**
		* Change the index of the IGraphicSkin object within this collection.  
		* 
		* @param value The IGraphicSkin object that is to be repositioned.
		* @param newIndex The position at which to place the IGraphicSkin object within the collection.
		* @return True if the operation is successful False if unsuccessful.   
		**/
		public function setItemIndex(value:IGraphicSkin,newIndex:Number):Boolean{
			return super._setItemIndex(value,newIndex);
		}
		
		/**
		* Adds a series of IGraphicSkin objects to this collection.  
		*
		* @param value The collection to be added to this IGraphicSkin collection.  
		* @return The resulting GraphicSkinCollection after the objects are added.   
		**/
		public function addItems(value:GraphicSkinCollection):GraphicSkinCollection{
			//todo
			super.concat(value.items)
			return this;
		}
		
		
		
		
		
	}
}