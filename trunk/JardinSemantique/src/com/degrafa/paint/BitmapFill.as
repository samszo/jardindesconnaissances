package com.degrafa.paint
{
	import com.degrafa.core.DegrafaObject;
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.core.Measure;
	
	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.DisplayObject;
	import flash.display.Graphics;
	import flash.display.Shape;
	import flash.display.Sprite;
	import flash.geom.Matrix;
	import flash.geom.Point;
	import flash.geom.Rectangle;
	import flash.utils.getDefinitionByName;
	
	[DefaultProperty("source")]
	[Bindable(event="propertyChange")]
	
	/**
	 * Used to fill an area on screen with a bitmap or other DisplayObject.
	 */
	public class BitmapFill extends DegrafaObject implements IGraphicsFill
	{
		
		// static constants
		public static const NONE:String = "none";
		public static const REPEAT:String = "repeat";
		public static const SPACE:String = "space";
		public static const STRETCH:String = "stretch";
		
		// private variables
		private var sprite:Sprite;
		private var target:DisplayObject;
		private var bitmapData:BitmapData;
		
		// property backing variables
		private var _originX:Number = 0;
		private var _originY:Number = 0;
		private var _offsetX:Measure = new Measure();
		private var _offsetY:Measure = new Measure();
		private var _repeatX:String = "repeat";
		private var _repeatY:String = "repeat";
		private var _rotation:Number = 0;
		private var _scaleX:Number = 1;
		private var _scaleY:Number = 1;
		private var _smooth:Boolean = false;
		
		public function BitmapFill(source:Object = null):void {
			this.source = source;
		}
		
		/**
		 * The horizontal origin for the bitmap fill.
		 * The bitmap fill is offset so that this point appears at the origin.
		 * Scaling and rotation of the bitmap are performed around this point.
		 * @default 0
		 */
		public function get originX():Number { return _originX; }
		public function set originX(value:Number):void {
			if(_originX != value) {
				initChange("originX", _originX, _originX = value, this);
			}
		}
		
		
		/**
		 * The vertical origin for the bitmap fill.
		 * The bitmap fill is offset so that this point appears at the origin.
		 * Scaling and rotation of the bitmap are performed around this point.
		 * @default 0
		 */
		public function get originY():Number { return _originY; }
		public function set originY(value:Number):void {
			if(_originY != value) {
				initChange("originY", _originY, _originY = value, this);
			}
		}
		
		
		/**
		 * How far the bitmap is horizontally offset from the origin.
		 * This adjustment is performed after rotation and scaling.
		 * @default 0
		 */
		public function get offsetX():Number { return _offsetX.value; }
		public function set offsetX(value:Number):void {
			if(_offsetX.value != value) {
				initChange("offsetX", _offsetX.value, _offsetX.value = value, this);
			}
		}
		
		/**
		 * The unit of measure corresponding to offsetX.
		 */
		public function get offsetXUnit():String { return _offsetX.unit; }
		public function set offsetXUnit(value:String):void {
			if(_offsetX.unit != value) {
				initChange("offsetXUnit", _offsetX.unit, _offsetX.unit = value, this);
			}
		}
		
		
		/**
		 * How far the bitmap is vertically offset from the origin.
		 * This adjustment is performed after rotation and scaling.
		 * @default 0
		 */
		public function get offsetY():Number { return _offsetY.value; }
		public function set offsetY(value:Number):void {
			if(_offsetY.value != value) {
				initChange("offsetY", _offsetY.value, _offsetY.value = value, this);
			}
		}
		
		/**
		 * The unit of measure corresponding to offsetY.
		 */
		public function get offsetYUnit():String { return _offsetY.unit; }
		public function set offsetYUnit(value:String):void {
			if(_offsetY.unit != value) {
				initChange("offsetYUnit", _offsetY.unit, _offsetY.unit = value, this);
			}
		}
		
		/**
		 * How the bitmap repeats horizontally.
		 * Valid values are "none", "repeat", "space", and "stretch".
		 * @default "repeat"
		 */
		public function get repeatX():String { return _repeatX; }
		public function set repeatX(value:String):void {
			if(_repeatX != value) {
				initChange("repeatX", _repeatX, _repeatX = value, this);
			}
		}
		
		
		/**
		 * How the bitmap repeats vertically.
		 * Valid values are "none", "repeat", "space", and "stretch".
		 * @default "repeat"
		 */
		public function get repeatY():String { return _repeatY; }
		public function set repeatY(value:String):void {
			if(_repeatY != value) {
				initChange("repeatY", _repeatY, _repeatY = value, this);
			}
		}
		
		
		/**
		 * The number of degrees to rotate the bitmap.
		 * Valid values range from 0.0 to 360.0.
		 * @default 0
		 */
		public function get rotation():Number { return _rotation; }
		public function set rotation(value:Number):void {
			if(_rotation != value) {
				initChange("rotation", _rotation, _rotation = value, this);
			}
		}
		
		
		/**
		 * The percent to horizontally scale the bitmap when filling, from 0.0 to 1.0.
		 * If 1.0, the bitmap is filled at its natural size.
		 * @default 1.0
		 */
		public function get scaleX():Number { return _scaleX; }
		public function set scaleX(value:Number):void {
			if(_scaleX != value ) {
				initChange("scaleX", _scaleX, _scaleX = value, this);
			}
		}
		
		
		/**
		 * The percent to vertically scale the bitmap when filling, from 0.0 to 1.0.
		 * If 1.0, the bitmap is filled at its natural size.
		 * @default 1.0
		 */
		public function get scaleY():Number { return _scaleY; }
		public function set scaleY(value:Number):void {
			if(_scaleY != value) {
				initChange("scaleY", _scaleY, _scaleY = value, this);
			}
		}
		
		
		/**
		 * A flag indicating whether to smooth the bitmap data when filling with it.
		 * @default false
		 */
		public function get smooth():Boolean { return _smooth; }
		public function set smooth(value:Boolean):void {
			if(value != smooth) {
				initChange("smooth", _smooth, _smooth = value, this);
			}
		}
		
		
		/**
		 * The source used for the bitmap fill.
		 * The fill can render from various graphical sources, including the following: 
		 * A Bitmap or BitmapData instance. 
		 * A class representing a subclass of DisplayObject. The BitmapFill instantiates the class and creates a bitmap rendering of it. 
		 * An instance of a DisplayObject. The BitmapFill copies it into a Bitmap for filling. 
		 * The name of a subclass of DisplayObject. The BitmapFill loads the class, instantiates it, and creates a bitmap rendering of it.
		 **/
		public function get source():Object { return bitmapData; }
		public function set source(value:Object):void {
			//_source = value;
			var oldValue:Object = bitmapData;
			
			target = null;
			bitmapData = null;
			
			if (!value) {
				return;
			}
			
			if (value is BitmapData)
			{
				bitmapData = value as BitmapData;
				initChange("source", oldValue, bitmapData, this);
				return;
			}
			
			//var sprite:DisplayObject;
			if (value is Class)
			{
				//var cls:Class = value as Class;
				target = new value();
				//if(target is Bitmap) {
					sprite = new Sprite();
					sprite.addChild(target);
				//}
			}
			else if (value is Bitmap)
			{
				bitmapData = value.bitmapData;
				target = value as Bitmap;
			}
			else if (value is DisplayObject)
			{
				target = value as DisplayObject;
			}
			else if (value is String)
			{
				var cls:Class = Class(getDefinitionByName(value as String));
				target = new cls();
			}
			else
			{
				initChange("source", oldValue, null, this);
				return;
			}
				
			if(bitmapData == null && target != null)
			{
				bitmapData = new BitmapData(target.width, target.height, true, 0);
				bitmapData.draw(target);
			}
			
			initChange("source", oldValue, bitmapData, this);
		}
		
		
		public function begin(graphics:Graphics, rectangle:Rectangle):void {
			
			if(!bitmapData) {
				return;
			}
			
			// todo: optimize all this with cacheing
			
			var template:BitmapData = bitmapData;
			
			var repeat:Boolean = true;
			var positionX:Number = 0; 
			var positionY:Number = 0;
			
			var matrix:Matrix = new Matrix();
			matrix.translate(rectangle.x, rectangle.y);
			
			// deal with stretching
			if(repeatX == BitmapFill.STRETCH || repeatY == BitmapFill.STRETCH) {
				var stretchX:Number = repeatX == STRETCH ? rectangle.width : template.width;
				var stretchY:Number = repeatY == STRETCH ? rectangle.height : template.height;
				if(target) {
					target.width = stretchX;
					target.height = stretchY;
					template = new BitmapData(stretchX, stretchY, true, 0);
					// use sprite to render 9-slice Bitmap
					if(sprite) { 
						template.draw(sprite);
					} else {
						template.draw(target);
					}
				} else {
					matrix.scale(stretchX/template.width, stretchY/template.height);
				}
			}
			
			// deal with spacing
			if(repeatX == BitmapFill.SPACE || repeatY == BitmapFill.SPACE) {
				// todo: account for rounding issues here
				var spaceX:Number = repeatX == BitmapFill.SPACE ? Math.round((rectangle.width % template.width) / int(rectangle.width/template.width)) : 0;
				var spaceY:Number = repeatY == BitmapFill.SPACE ? Math.round((rectangle.height % template.height) / int(rectangle.height/template.height)) : 0;
				var pattern:BitmapData = new BitmapData(Math.round(spaceX+template.width), Math.round(spaceY+template.height), true, 0);
				pattern.copyPixels(template, template.rect, new Point(Math.round(spaceX/2), Math.round(spaceY/2)));
				template = pattern;
			} 
			
			if(repeatX == BitmapFill.NONE || repeatX == BitmapFill.REPEAT) {
				positionX = _offsetX.relativeTo(rectangle.width-template.width)
			}
			
			if(repeatY == BitmapFill.NONE || repeatY == BitmapFill.REPEAT) {
				positionY = _offsetY.relativeTo(rectangle.height-template.height)
			}
			
			// deal with repeating (or no-repeating rather)
			if(repeatX == BitmapFill.NONE || repeatY == BitmapFill.NONE) {
				var area:Rectangle = new Rectangle(1, 1, rectangle.width, rectangle.height);
				var areaMatrix:Matrix = new Matrix();
				
				if(repeatX == BitmapFill.NONE) {
					area.width = template.width
				} else {
					areaMatrix.translate(positionX, 0)
					positionX = 0;
				}
				
				if(repeatY == BitmapFill.NONE) {
					area.height = template.height
				} else {
					areaMatrix.translate(0, positionY);
					positionY = 0;
				}
				
				// repeat onto a shape as needed
				var shape:Shape = new Shape(); // todo: cache for performance
				shape.graphics.beginBitmapFill(template, areaMatrix);
				shape.graphics.drawRect(0, 0, area.width, area.height);
				shape.graphics.endFill();
				
				// use the shape to create a new template (with transparent edges)
				template = new BitmapData(area.width+2, area.height+2, true, 0);
				template.draw(shape, new Matrix(1, 0, 0, 1, 1, 1), null, null, area);
				
				repeat = false;
			}
			
			matrix.translate(-_originX,-_originY);
			matrix.scale(_scaleX,_scaleY);
			matrix.rotate(_rotation);
			matrix.translate(positionX, positionY);
			
			graphics.beginBitmapFill(template, matrix, repeat, smooth);
		}
		
		public function end(graphics:Graphics):void {
			graphics.endFill();
		}
		
	}
}