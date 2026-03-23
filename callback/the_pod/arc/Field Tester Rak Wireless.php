<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

<script>

function Decoder(bytes, port) {
	var decoded = {};
	// avoid sending Downlink ACK to integration (Cargo)
	if (port === 1) {
		var lonSign = (bytes[0] >> 7) & 0x01 ? -1 : 1;
		var latSign = (bytes[0] >> 6) & 0x01 ? -1 : 1;

		var encLat = ((bytes[0] & 0x3f) << 17) +
			(bytes[1] << 9) +
			(bytes[2] << 1) +
			(bytes[3] >> 7);

		var encLon = ((bytes[3] & 0x7f) << 16) +
			(bytes[4] << 8) +
			bytes[5];

		var hdop = bytes[8] / 10;
		var sats = bytes[9];

		const maxHdop = 0;
		const minSats = 0;

		if ((hdop < maxHdop) && (sats >= minSats)) {
			// Send only acceptable quality of position to mappers
			decoded.latitude = latSign * (encLat * 108 + 53) / 10000000;
			decoded.longitude = lonSign * (encLon * 215 + 107) / 10000000;
			decoded.altitude = ((bytes[6] << 8) + bytes[7]) - 1000;
			decoded.accuracy = (hdop * 5 + 5) / 10
			decoded.hdop = hdop;
			decoded.sats = sats;
		} else {
			decoded.error = "Need more GPS precision (hdop must be <" + maxHdop +
				" & sats must be >= " + minSats + ") current hdop: " + hdop + " & sats:" + sats;
		}
		return decoded;
	}
	return null;
}


function base64ToArrayBuffer(base64) {
    var binaryString = atob(base64);
    var bytes = new Uint8Array(binaryString.length);
    for (var i = 0; i < binaryString.length; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
}

var bytes = base64ToArrayBuffer("TiT2qOWRA/cSBg==");

var payload = Decoder(bytes, 1);

console.log(payload);

</script>



</body>
</html>