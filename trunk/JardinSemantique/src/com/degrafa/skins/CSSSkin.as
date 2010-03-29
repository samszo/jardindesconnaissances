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
package com.degrafa.skins
{
	
	import com.degrafa.core.Measure;
	import com.degrafa.core.utils.StyleUtil;
	import com.degrafa.geometry.AdvancedRectangle;
	import com.degrafa.paint.BitmapFill;
	import com.degrafa.paint.BlendFill;
	import com.degrafa.paint.ComplexFill;
	
	import flash.geom.Rectangle;
	
	import mx.core.EdgeMetrics;
	import mx.graphics.GradientEntry;
	import mx.graphics.IFill;
	import mx.graphics.LinearGradient;
	import mx.graphics.SolidColor;
	import mx.skins.Border;
	
	
	//*************************************************
	// Style MetaData
	//*************************************************
	
	// CSS3 Based Stles
	// http://www.w3.org/TR/2005/WD-css3-background-20050216/
	
	/**
	 * Sets the background color of an element.
	 */
	[Style(name="backgroundColor")]
	
	// woops
	// Images are drawn with the first specified one on top (closest to the user) and each subsequent image behind the previous one.
	
	/**
	 * Sets the background image(s) of an element, or 'none' for no image..
	 * The 'background-color' is painted below all the images. 
	 */
	[Style(name="backgroundImage", enumeration="none")] // need to implement none
	
	/**
	 * Values have the following meanings:
	 * <dl>
	 * <dt>repeat-x</dt> 
	 * <dd>Equivalent to 'repeat no-repeat'.</dd>
	 * <dt>repeat-y</dt> 
	 * <dd>Equivalent to 'no-repeat repeat'</dd>
	 * </dl> 
	 * Otherwise, if there is one value, it sets both the horizontal and vertical repeat. If there are two, the first one is for the horizontal direction, the second for the vertical one. As follows:
	 * <dl>
	 * <dt>repeat</dt>
	 * <dd>The image is repeated in this direction as often as needed to cover the area. The image is clipped at the border or padding edge (depending on 'background-clip').</dd>
	 * <dt>no-repeat</dt>
	 * <dd>The image is not repeated in this direction. If it is too large, the image is clipped at the border or padding edge (depending on 'background-clip').</dd>
	 * <dt>space</dt>
	 * <dd>The image is repeated as often as it will fit without being clipped and then the images are spaced out to fill the area. 
	 * The value of 'background-position' for this direction is ignored.</dd>
	 * </dl>
	 * @default "repeat"
	 */
	[Style(name="backgroundRepeat", enumeration="repeat,repeat-x,repeat-y,no-repeat,space")]
	
	// [Style(name="backgroundAttachment", enumeration="scroll,fixed,local")] // requires consideration
	
	/**
	 * If a background image has been specified, this property specifies its initial position.
	 */
	[Style(name="backgroundPosition", enumeration="top,bottom,left,right,center")]
	
	// [Style(name="backgroundClip", enumeration="border,padding")] // not yet implemented
	// [Style(name="backgroundOrigin", enumeration="border,padding,content")] // not yet implemented
	// [Style(name="backgroundSize", enumeration="auto,round")] // not yet implemented (? conflict with repeat: stretch ?)
	// [Style(name="backgroundBreak", enumeration="bounding-box,each-box,continuous")] // technical limitation?
	
	
	// [Style(name="border")] // not yet implemented
	// [Style(name="borderTop")] // not yet implemented
	// [Style(name="borderRight")] // not yet implemented
	// [Style(name="borderBottom")] // not yet implemented
	// [Style(name="borderLeft")] // not yet implemented
	
	/**
	 * Shorthand that sets the four 'border-*-width' properties.
	 * If it has four values, they set top, right, bottom and left in that order. If left is missing, it is the same as right; if bottom is missing, it is the same as top; if right is missing, it is the same as top.
	 */
	[Style(name="borderWidth")]
	
	[Style(name="borderTopWidth")]
	[Style(name="borderRightWidth")]
	[Style(name="borderBottomWidth")]
	[Style(name="borderLeftWidth")]
	
	// [Style(name="borderRadius")] // todo: check standard
	
	[Style(name="borderTopRightRadius")]
	[Style(name="borderBottomRightRadius")]
	[Style(name="borderBottomLeftRadius")]
	[Style(name="borderTopLeftRadius")]
	
	/**
	 * Shorthand for the four 'border-*-color' properties.
	 * The four values set the top, right, bottom and left border, respectively.
	 * A missing left is the same as right, a missing bottom is the same as top, and a missing right is also the same as top.
	 * <strong>Note</strong>: space delimited shorthand properties must be surrounded by quotation marks in Flex CSS.
	 */
	[Style(name="borderColor")]
	[Style(name="borderTopColor")]
	[Style(name="borderRightColor")]
	[Style(name="borderBottomColor")]
	[Style(name="borderLeftColor")]
	
	// [Style(name="boxShadow")] // not yet implemented
	
	
	// Custom (non-w3c based) Styles
	
	/**
	 * Sets the blend mode used to blend a given image layer with the layers behind it.
	 * @defaul "normal"
	 */
	[Style(name="backgroundBlend", enumeration="add,alpha,darken,difference,erase,hardlight,invert,layer,lighten,multiply,normal,overlay,screen,subtract")]
	
	
	/**
	 * A Flex skin for applying advanced styling through W3C based CSS.
	 */
	public class CSSSkin extends Border
	{
		
		
		//*****************************************************
		// Private Constants (magic strings)
		//*****************************************************
		
		private static const ASSET_CLASS:String = "assetClass";
		private static const BACKGROUND_COLOR:String = "backgroundColor";
		private static const BACKGROUND_IMAGE:String = "backgroundImage";
		private static const BACKGROUND_REPEAT:String = "backgroundRepeat";
		private static const BACKGROUND_POSITION:String = "backgroundPosition";
		private static const BOX_SHADOW:String = "boxShadow";
		private static const BACKGROUND_BLEND:String = "backgroundBlend";
		private static const BORDER_ALPHA:String = "borderAlpha";
		private static const BORDER_TOP_WIDTH:String = "borderTopWidth";
		private static const BORDER_RIGHT_WIDTH:String = "borderRightWidth";
		private static const BORDER_BOTTOM_WIDTH:String = "borderBottomWidth";
		private static const BORDER_LEFT_WIDTH:String = "borderLeftWidth";
		private static const BORDER_WIDTH:String = "borderWidth";
		private static const BORDER_COLOR:String = "borderColor";
		private static const BORDER_TOP_COLOR:String = "borderTopColor";
		private static const BORDER_RIGHT_COLOR:String = "borderRightColor";
		private static const BORDER_BOTTOM_COLOR:String = "borderBottomColor";
		private static const BORDER_LEFT_COLOR:String = "borderLeftColor";
		private static const BORDER_TOP_RIGHT_RADIUS:String = "borderTopRightRadius";
		private static const BORDER_BOTTOM_RIGHT_RADIUS:String = "borderBottomRightRadius";
		private static const BORDER_BOTTOM_LEFT_RADIUS:String = "borderBottomLeftRadius";
		private static const BORDER_TOP_LEFT_RADIUS:String = "borderTopLeftRadius";
		
		
		//**********************************************************
		// Private Variables
		//**********************************************************
		
		private var assetClass:Class;
		private var box:AdvancedRectangle = new AdvancedRectangle();
		
		private var background:ComplexFill; // all background layers
		private var backgroundColor:IFill; // complex or simple
		private var backgroundImage:IFill; // complex or simple
		private var backgroundPosition:Array = []; // of {x:Measure, y:Measure}
		private var backgroundRepeat:Array; // of {x:String, y:String}
		private var backgroundBlend:Array; // of strings
		private var boxShadow:Array; // of filters
		
		private var borderTopWidth:Measure;
		private var borderRightWidth:Measure;
		private var borderBottomWidth:Measure;
		private var borderLeftWidth:Measure;
		
		private var borderTopFill:IFill;
		private var borderRightFill:IFill;
		private var borderBottomFill:IFill;
		private var borderLeftFill:IFill;
		
		private var borderTopRightRadius:Object; // {x:Measure, y:Measure, curve:Measure}
		private var borderBottomRightRadius:Object; // {x:Measure, y:Measure, curve:Measure}
		private var borderBottomLeftRadius:Object; // {x:Measure, y:Measure, curve:Measure}
		private var borderTopLeftRadius:Object; // {x:Measure, y:Measure, curve:Measure}
		
		private var assetClassChanged:Boolean;
		private var backgroundChanged:Boolean;
		private var backgroundColorChanged:Boolean;
		private var backgroundImageChanged:Boolean;
		private var backgroundPositionChanged:Boolean;
		private var backgroundRepeatChanged:Boolean;
		private var backgroundBlendChanged:Boolean;
		private var boxShadowChanged:Boolean;
		private var borderTopWidthChanged:Boolean;
		private var borderRightWidthChanged:Boolean;
		private var borderBottomWidthChanged:Boolean;
		private var borderLeftWidthChanged:Boolean;
		private var borderWidthChanged:Boolean;
		private var borderTopRightRadiusChanged:Boolean;
		private var borderBottomRightRadiusChanged:Boolean;
		private var borderBottomLeftRadiusChanged:Boolean;
		private var borderTopLeftRadiusChanged:Boolean;
		private var borderMetricsChanged:Boolean;
		private var borderColorChanged:Boolean;
		private var borderTopColorChanged:Boolean;
		private var borderRightColorChanged:Boolean;
		private var borderBottomColorChanged:Boolean;
		private var borderLeftColorChanged:Boolean;
		
		
		//********************************************************
		// Public Properties
		//********************************************************
		
		private var _borderMetrics:EdgeMetrics;
		override public function get borderMetrics():EdgeMetrics {
			if(borderMetricsChanged) {
				//updateBorderMetrics();
				borderMetricsChanged = false;
			}
			return _borderMetrics;
		}
		
		
		//*********************************************************
		// Constructor
		//*********************************************************
		
		public function CSSSkin():void {
			super();
			_borderMetrics = EdgeMetrics.EMPTY;
			background = new ComplexFill();
			refreshStyles();
		}
		
		
		//************************************************************
		// Invalidation Framework
		//************************************************************
		
		override public function styleChanged(styleProp:String):void {
			super.styleChanged(styleProp);
			switch(styleProp) {
				case ASSET_CLASS:
					assetClassChanged = true;
					break;
				case BACKGROUND_COLOR:
					backgroundColorChanged = true;
					invalidateDisplayList();
					break;
				case BACKGROUND_IMAGE:
					backgroundImageChanged = true;
					invalidateDisplayList();
					break;
				case BACKGROUND_REPEAT:
					backgroundRepeatChanged = true;
					invalidateDisplayList();
					break;
				case BACKGROUND_POSITION:
					backgroundPositionChanged = true;
					invalidateDisplayList();
					break;
				case BACKGROUND_BLEND:
					backgroundBlendChanged = true;
					invalidateDisplayList();
					break;
				case BOX_SHADOW:
					boxShadowChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_ALPHA:
					//var style:Object = getStyle(BORDER_ALPHA);
					invalidateDisplayList();
					break;
				case BORDER_TOP_WIDTH:
					borderTopWidthChanged = true;
					borderMetricsChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_RIGHT_WIDTH:
					borderRightWidthChanged = true;
					borderMetricsChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_BOTTOM_WIDTH:
					borderBottomWidthChanged = true;
					borderMetricsChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_LEFT_WIDTH:
					borderLeftWidthChanged = true;
					borderMetricsChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_WIDTH:
					borderWidthChanged = true;
					borderMetricsChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_TOP_RIGHT_RADIUS:
					borderTopRightRadiusChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_BOTTOM_RIGHT_RADIUS:
					borderBottomRightRadiusChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_BOTTOM_LEFT_RADIUS:
					borderBottomLeftRadiusChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_TOP_LEFT_RADIUS:
					borderTopLeftRadiusChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_COLOR:
					borderColorChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_TOP_COLOR:
					borderTopColorChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_RIGHT_COLOR:
					borderRightColorChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_BOTTOM_COLOR:
					borderBottomColorChanged = true;
					invalidateDisplayList();
					break;
				case BORDER_LEFT_COLOR:
					borderLeftColorChanged = true;
					invalidateDisplayList();
					break;
			}
			
		}
		
		override protected function updateDisplayList(unscaledWidth:Number, unscaledHeight:Number):void {
			
			//super.updateDisplayList(unscaledWidth, unscaledHeight);
			commitStyles();
			graphics.clear();
			
			// AdvancedBox will do the actual drawing.
			// It will also optimize for performance where it can.
			if(borderLeftWidth != null) {
				box.leftWidth = borderLeftWidth.relativeTo(unscaledWidth);
			}
			if(borderTopWidth != null) {
				box.topWidth = borderTopWidth.relativeTo(unscaledHeight);
			}
			if(borderTopWidth != null) {
				box.rightWidth = borderRightWidth.relativeTo(unscaledWidth);
			}
			if(borderTopWidth != null) {
				box.bottomWidth = borderBottomWidth.relativeTo(unscaledHeight);
			}
			
			box.leftFill = borderLeftFill;
			box.topFill = borderTopFill;
			box.rightFill = borderRightFill;
			box.bottomFill = borderBottomFill;
			
			// needs calculation for fill somewhere
			/*
			var tr:LinearGradient = new LinearGradient();
			tr.entries = [new GradientEntry((box.topFill as SolidColor).color, 0, (box.topFill as SolidColor).alpha), new GradientEntry((box.rightFill as SolidColor).color, 1, (box.rightFill as SolidColor).alpha)];
			tr.angle = -15;
			
			var bl:LinearGradient = new LinearGradient();
			bl.entries = [new GradientEntry((box.leftFill as SolidColor).color, 0, (box.leftFill as SolidColor).alpha), new GradientEntry((box.bottomFill as SolidColor).color, 1, (box.bottomFill as SolidColor).alpha)];
			bl.angle = 45;
			*/
			box.topLeftFill = createTransition(box.leftFill, box.topFill, -45)
			box.topRightFill = createTransition(box.topFill, box.rightFill, 45); // box.rightFill;
			box.bottomLeftFill = createTransition(box.leftFill, box.bottomFill, 45); // box.leftFill;
			box.bottomRightFill = createTransition(box.bottomFill, box.rightFill, -45);
			
			box.topLeftRadiusX = (borderTopLeftRadius.x as Measure).relativeTo(unscaledWidth);
			box.topLeftRadiusY = (borderTopLeftRadius.y as Measure).relativeTo(unscaledHeight);
			box.topRightRadiusX = (borderTopRightRadius.x as Measure).relativeTo(unscaledWidth);
			box.topRightRadiusY = (borderTopRightRadius.y as Measure).relativeTo(unscaledHeight);
			box.bottomLeftRadiusX = (borderBottomLeftRadius.x as Measure).relativeTo(unscaledWidth);
			box.bottomLeftRadiusY = (borderBottomLeftRadius.y as Measure).relativeTo(unscaledHeight);
			box.bottomRightRadiusX = (borderBottomRightRadius.x as Measure).relativeTo(unscaledWidth);
			box.bottomRightRadiusY = (borderBottomRightRadius.y as Measure).relativeTo(unscaledHeight);
			box.draw(graphics, new Rectangle(0, 0, unscaledWidth, unscaledHeight));
			/*
			var vm:EdgeMetrics = new EdgeMetrics();
			if(borderTopWidth != null) {
				vm.top = borderTopWidth.relativeTo(unscaledHeight);
			}
			if(borderRightWidth != null) {
				vm.right = borderRightWidth.relativeTo(unscaledWidth);
			}
			if(borderBottomWidth != null) {
				vm.bottom = borderBottomWidth.relativeTo(unscaledHeight);
			}
			if(borderLeftWidth != null) {
				vm.left = borderLeftWidth.relativeTo(unscaledWidth);
			}
			_borderMetrics = vm;*/
		}
		
		// will act as commitProperties for our styles
		protected function commitStyles():void {
			if(assetClassChanged) {
				updateAssetClass();
				assetClassChanged = true;
			}
			var backgroundChanged:Boolean = false;
			if(backgroundColorChanged) {
				updateBackgroundColor();
				backgroundColorChanged = false;
				backgroundChanged = true;
			}
			if(backgroundImageChanged) {
				updateBackgroundImage();
				backgroundImageChanged = false;
				backgroundChanged = true;
			}
			if(backgroundRepeatChanged) {
				updateBackgroundRepeat();
				backgroundRepeatChanged = false;
			}
			if(backgroundPositionChanged) {
				updateBackgroundPosition();
				backgroundPositionChanged = false;
			}
			if(backgroundBlendChanged) {
				updateBackgroundBlend();
				backgroundBlendChanged = false;
				backgroundChanged = true;
			}
			if(boxShadowChanged) {
				updateBoxShadow();
				boxShadowChanged = false;
			}
			if(borderWidthChanged) {
				updateBorderWidth();
				borderWidthChanged = false;
			}
			if(borderTopWidthChanged) {
				updateBorderSide(BORDER_TOP_WIDTH);
				borderTopWidthChanged = false;
			}
			if(borderRightWidthChanged) {
				updateBorderSide(BORDER_RIGHT_WIDTH);
				borderRightWidthChanged = false;
			}
			if(borderBottomWidthChanged) {
				updateBorderSide(BORDER_BOTTOM_WIDTH);
				borderBottomWidthChanged = false;
			}
			if(borderLeftWidthChanged) {
				updateBorderSide(BORDER_LEFT_WIDTH);
				borderLeftWidthChanged = false;
			}
			if(borderTopRightRadiusChanged) {
				updateBorderRadius(BORDER_TOP_RIGHT_RADIUS);
				borderTopRightRadiusChanged = false;
			}
			if(borderBottomRightRadiusChanged) {
				updateBorderRadius(BORDER_BOTTOM_RIGHT_RADIUS);
				borderBottomRightRadiusChanged = false;
			}
			if(borderBottomLeftRadiusChanged) {
				updateBorderRadius(BORDER_BOTTOM_LEFT_RADIUS);
				borderBottomLeftRadiusChanged = false;
			}
			if(borderTopLeftRadiusChanged) {
				updateBorderRadius(BORDER_TOP_LEFT_RADIUS);
				borderTopLeftRadiusChanged = false;
			}
			applyBackgroundImageSettings();
			if(backgroundChanged) {
				background.fills = new Array();
				ComplexFill.add(backgroundColor, background);
				ComplexFill.add(backgroundImage, background);
			}
			box.backgroundFill = background;
			if(borderColorChanged) {
				updateBorderColor();
				borderColorChanged = false;
			}
			if(borderTopColorChanged) {
				updateBorderFill(BORDER_TOP_COLOR);
				borderTopColorChanged = false;
			}
			if(borderRightColorChanged) {
				updateBorderFill(BORDER_RIGHT_COLOR);
				borderRightColorChanged = false;
			}
			if(borderBottomColorChanged) {
				updateBorderFill(BORDER_BOTTOM_COLOR);
				borderBottomColorChanged = false;
			}
			if(borderLeftColorChanged) {
				updateBorderFill(BORDER_LEFT_COLOR);
				borderLeftColorChanged = false;
			}
		}
		
		
		//*******************************************************************
		// Utility Functions
		//*******************************************************************
		
		private function updateAssetClass():void {
			var style:Object = getStyle(ASSET_CLASS);
			if(style is Class) {
				assetClass = style as Class;
			} else {
				assetClass = null;
			}
		}
		
		private function updateBorderColor():void {
			var style:Object = getStyle(BORDER_COLOR);
			var styles:Array = StyleUtil.expandProperty(style, [0, -1, -2, -2]);
			for(var i:int = 0; i < 4; i++) {
				var fill:IFill = StyleUtil.resolveFill(styles[i]);
				(fill as SolidColor).alpha = getStyle(BORDER_ALPHA);
				switch(i) {
					case 0:
						borderLeftFill = fill;
						break;
					case 1:
						borderTopFill = fill;
						break;
					case 2:
						borderRightFill = fill;
						break;
					case 3:
						borderBottomFill = fill;
						break;
				}
			}
		}
		
		private function updateBackgroundColor():void {
			var style:Object = getStyle(BACKGROUND_COLOR);
			backgroundColor = StyleUtil.resolveFill(style);
		}
		
		private function updateBackgroundImage():void {
			var style:Object = resolveAssets(getStyle(BACKGROUND_IMAGE));
			backgroundImage = StyleUtil.resolveFill(style);
		}
		
		private function updateBackgroundRepeat():void {
			var style:Object = getStyle(BACKGROUND_REPEAT);
			backgroundRepeat = StyleUtil.resolveRepeat(style, []);
		}
		
		private function updateBackgroundPosition():void {
			var style:Object = getStyle(BACKGROUND_POSITION);
			backgroundPosition = StyleUtil.resolvePosition(style, []);
		}
		
		private function updateBackgroundBlend():void {
			var style:Object = getStyle(BACKGROUND_BLEND);
			if(style is String) {
				backgroundBlend = [style]
			} else if(style is Array) {
				backgroundBlend = style as Array;
			} else {
				backgroundBlend = [];
			}
		}
		
		private function updateBoxShadow():void {
			cleanFilters(boxShadow);
			var style:Object = getStyle(BOX_SHADOW);
			boxShadow = StyleUtil.resolveShadow(style);
			applyFilters(boxShadow);
		}
		
		// border updates
		
		private function updateBorderWidth():void {
			var style:Object = getStyle(BORDER_WIDTH);
			if(style != null) {
				var properties:Array = StyleUtil.expandProperty(style, [0, -1, -2, -2]);
				for(var i:int = 0; i < 4; i++) {
					var measure:Measure = StyleUtil.resolveMeasure(properties[i]);
					switch(i) {
						case 0:
							borderLeftWidth = measure;
							break;
						case 1:
							borderTopWidth = measure;
							break;
						case 2:
							borderRightWidth = measure;
							break;
						case 3:
							borderBottomWidth = measure;
							break;
					}
				}
			}
		}
		
		private function updateBorderSide(token:String):void {
			var style:Object = getStyle(token);
			var measure:Measure = StyleUtil.resolveMeasure(style);
			/*if(measure == null) {
				measure = new Measure(0);
			}*/
			//if(measure != null) {
				switch(token) {
					case BORDER_LEFT_WIDTH:
						borderLeftWidth = measure;
						break;
					case BORDER_TOP_WIDTH:
						borderTopWidth = measure;
						break;
					case BORDER_RIGHT_WIDTH:
						borderRightWidth = measure;
						break;
					case BORDER_BOTTOM_WIDTH:
						borderBottomWidth = measure;
						break;
				}
			//}
		}
		
		private function updateBorderRadius(token:String):void {
			var style:Object = getStyle(token);
			var radius:Object = StyleUtil.resolveRadius(style);
			switch(token) {
				case BORDER_TOP_LEFT_RADIUS:
					borderTopLeftRadius = radius;
					break;
				case BORDER_TOP_RIGHT_RADIUS:
					borderTopRightRadius = radius;
					break;
				case BORDER_BOTTOM_LEFT_RADIUS:
					borderBottomLeftRadius = radius;
					break;
				case BORDER_BOTTOM_RIGHT_RADIUS:
					borderBottomRightRadius = radius;
					break;
			}
		}
		
		private function updateBorderFill(token:String):void {
			var style:Object = getStyle(token);
			//var fill:IFill = StyleUtil.resolveFill(style, new SolidColor(0xE2E2E2, 0.4));
			var fill:IFill = StyleUtil.resolveFill(style, new SolidColor(0xFFFFFF, 1));
			(fill as SolidColor).alpha = getStyle(BORDER_ALPHA);
			switch(token) {
				case BORDER_TOP_COLOR:
					//borderTopFill = fill;
					break;
				case BORDER_RIGHT_COLOR:
					//borderRightFill = fill;
					break;
				case BORDER_BOTTOM_COLOR:
					//borderBottomFill = fill;
					break;
				case BORDER_LEFT_COLOR:
					//borderLeftFill = fill;
					break;
			}
		}
		
		private function applyBackgroundImageSettings():void {
			if(backgroundImage is ComplexFill) {
				var complex:ComplexFill = backgroundImage as ComplexFill;
				var length:int = complex.fills.length;
				for(var i:int = 0; i < length; i++) {
					var fill:IFill = complex.fills[i] as IFill;
					if(fill is BitmapFill) {
						applyRepeat(fill as BitmapFill, backgroundRepeat[i]);
						applyPosition(fill as BitmapFill, backgroundPosition[i]);
					}
					if(backgroundBlend[i] is String) {
						fill = new BlendFill(fill, backgroundBlend[i] as String);
					}
					complex.fills[i] = fill;
				}
			} else if(backgroundImage is BitmapFill) {
				if(backgroundRepeat != null) {
					applyRepeat(backgroundImage as BitmapFill, backgroundRepeat[0]);
				}
				if(backgroundPosition != null) {
					applyPosition(backgroundImage as BitmapFill, backgroundPosition[0]);
				}
				if(backgroundBlend != null && backgroundBlend[0] is String) {
					backgroundImage = new BlendFill(backgroundImage, backgroundBlend[0] as String);
				}
			} else if(backgroundImage != null) {
				if(backgroundBlend[0] is String) {
					backgroundImage = new BlendFill(backgroundImage, backgroundBlend[0] as String);
				}
			}
		}
		
		private function applyRepeat(fill:BitmapFill, repeat:Object):void {
			if(repeat != null) {
				fill.repeatX = repeat.x as String;
				fill.repeatY = repeat.y as String;
			} else {
				fill.repeatX = BitmapFill.REPEAT;
				fill.repeatY = BitmapFill.REPEAT;
			}
		}
		
		private function applyPosition(fill:BitmapFill, position:Object):void {
			var positionX:Measure;
			var positionY:Measure;
			if(position != null) {
				positionX = position.x as Measure;
				positionY = position.y as Measure;
			} else {
				positionX = new Measure();
				positionY = new Measure();
			}
			fill.offsetX = positionX.value;
			fill.offsetXUnit = positionX.unit;
			fill.offsetY = positionY.value;
			fill.offsetYUnit = positionY.unit;
		}
		
		private function applyBlend(fill:IFill, blendMode:String):void {
			fill = new BlendFill(fill, blendMode);
		}
		
		private function resolveAssets(value:Object):Object {
			if(value is Array) {
				return resolveAssetsFromArray(value as Array);
			} else if(value is String) {
				return resolveAssetsFromString(value as String);
			} else {
				return value;
			}
		}
		
		private function resolveAssetsFromArray(value:Array):Array {
			var assets:Array = new Array();
			for each(var item:Object in value) {
				if(item is String) {
					assets.push(resolveAssetsFromString(item as String));
				} else {
					assets.push(item);
				}
			}
			return assets;
		}
		
		private function resolveAssetsFromString(value:String):Object {
			var asset:Object = assetClass[value];
			if(asset != null) {
				return asset;
			} else {
				return value;
			}
		}
		
		private function cleanFilters(filters:Array):void {
			if(parent) {
				var temp:Array = parent.filters;
				for each(var item:Object in filters) {
					var index:int = temp.indexOf(item);
					if(index >= 0) {
						temp.splice(index, 1);
					}
				}
				//parent.filters = temp;
			}
		}
		
		private function createTransition(fill1:IFill, fill2:IFill, angle:Number):IFill {
			if(fill1 is SolidColor && fill2 is SolidColor) {
				var fill:LinearGradient = new LinearGradient();
				fill.entries = [new GradientEntry((fill1 as SolidColor).color, 0, (fill1 as SolidColor).alpha), new GradientEntry((fill2 as SolidColor).color, 1, (fill2 as SolidColor).alpha)];
				fill.angle = angle;
				return fill;
			} else {
				return fill1;
			}
		}
		
		private function applyFilters(filters:Array):void {
			if(parent) {
				var temp:Array = parent.filters
				//parent.filters = temp.concat(filters);
			}
		}
		
		private function refreshStyles():void {
			styleChanged(ASSET_CLASS);
			styleChanged(BACKGROUND_COLOR);
			styleChanged(BACKGROUND_IMAGE);
			styleChanged(BACKGROUND_REPEAT);
			styleChanged(BACKGROUND_POSITION);
			styleChanged(BACKGROUND_BLEND);
			styleChanged(BOX_SHADOW);
			borderTopWidth = new Measure();
			borderRightWidth = new Measure();
			borderBottomWidth = new Measure();
			borderLeftWidth = new Measure();
			//styleChanged(BORDER_TOP_WIDTH);
			//styleChanged(BORDER_RIGHT_WIDTH);
			//styleChanged(BORDER_BOTTOM_WIDTH);
			//styleChanged(BORDER_LEFT_WIDTH);
			styleChanged(BORDER_ALPHA);
			styleChanged(BORDER_WIDTH);
			styleChanged(BORDER_TOP_RIGHT_RADIUS);
			styleChanged(BORDER_BOTTOM_RIGHT_RADIUS);
			styleChanged(BORDER_BOTTOM_LEFT_RADIUS);
			styleChanged(BORDER_TOP_LEFT_RADIUS);
			styleChanged(BORDER_COLOR);
			styleChanged(BORDER_TOP_COLOR);
			styleChanged(BORDER_RIGHT_COLOR);
			styleChanged(BORDER_BOTTOM_COLOR);
			styleChanged(BORDER_LEFT_COLOR);
		}
		
	}
}