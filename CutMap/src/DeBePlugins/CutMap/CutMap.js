var iG = false; // 인게임 여부
var cP = null; // 중심지점
var fP = null; // 첫지점
var sP = null; // 두번째지점
var lP = null; // 마지막지점 저장
var lB = "포함"; // 마지막지점포함 여부
makeButton();

function newLevel(hasLevel){
	iG = true;
}

function leaveGame(){
	iG = false;
}

function useItem(x, y, z, i, a, b, d){
	if (i == 351) { // Dye (염료)
		p = [x, y, z];
		e1 = "§d [CutMap] ";
		e2 = " 지점이 설정되지않았습니다..";
		switch (d) {
			case 5: // Purple
				cP = p;
				m = "중심";
			break;
			case 13: // Magenta
				if (cP == null) {
					clientMessage(e1 + "중심" + e2);
					return;
				}
				fP = p;
				m = "첫번째";
			break;
			case 9: // Pink
				if (cP == null) {
					clientMessage(e1 + "중심" + e2);
					return;
				} else if (fP == null) {
					clientMessage(e1 + "첫번째" + e2);
					return;
				}
				sP = p;
				lP = p;
				m = "두번째";
			break;
			defualt: return;
	}
	clientMessage("§c [CutMap] " + m + " 지점이 설정되었습니다. |:| X:" + p[0] + " Y:" + p[1] + " Z:" + p[2]);
}
}

function makeButton(){
ctx = com.mojang.minecraftpe.MainActivity.currentMainActivity.get();
ctx
		.runOnUiThread(new java.lang.Runnable({run : function(){
			try {
				dip = Math.ceil(ctx.getResources().getDisplayMetrics().density) * 100;
				makeW = new android.widget.PopupWindow();
				lastW = new android.widget.PopupWindow();
				makeB = new android.widget.Button(ctx);
				lastB = new android.widget.Button(ctx);
				makeL = new android.widget.RelativeLayout(ctx);
				lastL = new android.widget.RelativeLayout(ctx);
				makeB.setText("Make");
				lastB.setText("Last");
				makeB
						.setTextColor(android.graphics.Color
								.argb(100, 50, 50, 50));
				lastB
						.setTextColor(android.graphics.Color
								.argb(100, 50, 50, 50));
				makeB
						.setOnClickListener(new android.view.View.OnClickListener({onClick : function(v){
							try {
								e1 = "§d [CutMap] ";
								e2 = " 지점이 설정되지 않았습니다..";
								file = new java.io.File(android.os.Environment
										.getExternalStorageDirectory(), "Pocketmine/plugins/! DeBePlugins/CutMap.yml");
								if (!iG) {
									print(e1 + "게임내에서 실행해주세요.");
									return;
								} else if (cP == null) {
									clientMessage(e1 + "중심" + e2);
									return;
								} else if (fP == null) {
									clientMessage(e1 + "첫번째" + e2);
									return;
								} else if (sP == null) {
									clientMessage(e1 + "두번째" + e2);
									return;
								} else if (!file.exists()) {
									clientMessage(e1 + "CutMap 플러그인을 한번이라도 실행해주세요.");
									return;
								}
								x1 = fP[0];
								x2 = sP[0];
								y1 = fP[1];
								y2 = sP[1];
								z1 = fP[2];
								z2 = sP[2];
								if (fP[0] > sP[0]) {
									x1 = sP[0];
									x2 = fP[0];
								}
								if (fP[1] > sP[1]) {
									y1 = sP[1];
									y2 = fP[1];
								}
								if (fP[2] > sP[2]) {
									z1 = sP[2];
									z2 = fP[2];
								}
								yml = "--- \n";
								clientMessage("§d [CutMap] 저장 시작했습니다.");
								bc = 0;
								for (x = x1; x <= x2; x++) {
									for (y = y1; y <= y2; y++) {
										for (z = z1; z <= z2; z++) {
											if (lB == "포함") yml += " - " + (x - cP[0]) + " " + (y - cP[1]) + " " + (z - cP[2]) + " " + Level
													.getTile(x, y, z) + " " + Level
													.getData(x, y, z) + "\n";
											else if (x !== lP[0] && y !== lP[1] && z !== lP[2]) yml += " - " + (x - cP[0]) + " " + (y - cP[1]) + " " + (z - cP[2]) + " " + Level
													.getTile(x, y, z) + " " + Level
													.getData(x, y, z) + "\n";
											bc++;
										}
									}
								}
								yml += "...";
								clientMessage("§d [CutMap] 정렬 완료. 저장시작합니다." + bc + "블럭");
								bw = new java.io.BufferedWriter(new java.io.FileWriter(file));
								bw.write(yml);
								bw.close();
								clientMessage("§d [CutMap] 저장 완료했습니다." + bc + "블럭");
								print("§d [CutMap] 저장 완료했습니다.");
							} catch (err) {
								clientMessage("\n \n");
								clientMessage(err);
								print(err);
							}
						}}));
				lastB
						.setOnClickListener(new android.view.View.OnClickListener({onClick : function(v){
							if (!iG) {
								print("§d [CutMap] 게임내에서 실행해주세요.");
								return;
							} else if (lB == "제외") lB = "포함";
							else lB = "제외";
							clientMessage("§c [CutMap] 마지막 블럭을 " + lB);
						}}));
				makeB.setBackgroundDrawable(getDrawable(150, 0, 200, 255));
				lastB.setBackgroundDrawable(getDrawable(255, 255, 255, 150));
				makeL.addView(makeB);
				lastL.addView(lastB);
				makeW.setContentView(makeL);
				lastW.setContentView(lastL);
				makeW.setWidth(dip);
				lastW.setWidth(dip);
				makeW.setHeight(dip / 4);
				lastW.setHeight(dip / 4);
				makeW
						.setBackgroundDrawable(new android.graphics.drawable.ColorDrawable(android.graphics.Color.TRANSPARENT));
				lastW
						.setBackgroundDrawable(new android.graphics.drawable.ColorDrawable(android.graphics.Color.TRANSPARENT));
				makeW
						.showAtLocation(ctx.getWindow().getDecorView(), android.view.Gravity.LEFT | android.view.Gravity.TOP, 100, 50);
				lastW
						.showAtLocation(ctx.getWindow().getDecorView(), android.view.Gravity.LEFT | android.view.Gravity.TOP, 200, 50);
			} catch (err) {
				print(err);
			}
		}}));
}
function dip2px(dips){
return Math.ceil(dips * ctx.getResources().getDisplayMetrics().density);
}
function getDrawable(a, b, c, d){
bitmap = android.graphics.Bitmap
		.createBitmap(dip2px(45), dip2px(45), android.graphics.Bitmap.Config.ARGB_8888);
canvas = new android.graphics.Canvas(bitmap);
paint = new android.graphics.Paint();
for (i = 1; i < 23; i++) {
	paint.setARGB(a / i, b, c, d);
	canvas.drawCircle(dip2px(23), dip2px(23), dip2px(i), paint);
}
return new android.graphics.drawable.BitmapDrawable(bitmap);
}