<?php
/**
 * This code is free software; you can redistribute it and/or modify it under
 * the terms of the new BSD License.
 * 
 * @author     Sebastian Staudt
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package    Steam Condenser (PHP)
 * @subpackage Packets
 * @version    $Id$
 */

require_once "exceptions/PacketFormatException.php";
require_once "steam/packets/rcon/RCONGoldSrcResponse.php";
require_once "steam/packets/S2A_INFO_DETAILED_Packet.php";
require_once "steam/packets/A2S_INFO_Packet.php";
require_once "steam/packets/S2A_INFO2_Packet.php";
require_once "steam/packets/A2A_PING_Packet.php";
require_once "steam/packets/A2A_ACK_Packet.php";
require_once "steam/packets/A2S_PLAYER_Packet.php";
require_once "steam/packets/S2A_PLAYER_Packet.php";
require_once "steam/packets/A2S_RULES_Packet.php";
require_once "steam/packets/S2A_RULES_Packet.php";
require_once "steam/packets/A2S_SERVERQUERY_GETCHALLENGE_Packet.php";
require_once "steam/packets/S2C_CHALLENGE_Packet.php";
require_once "steam/packets/A2M_GET_SERVERS_BATCH2_Packet.php";
require_once "steam/packets/M2A_SERVER_BATCH_Packet.php";

/**
 * @package    Steam Condenser (PHP)
 * @subpackage Packets
 */
abstract class SteamPacketFactory
{
  /**
   * @param byte[] $rawData
   * @return SteamPacket
   */
  public static function getPacketFromData($rawData)
  {
    $header = ord($rawData[0]);
    $data = substr($rawData, 1);

    switch($header)
    {
      case SteamPacket::S2A_INFO_DETAILED_HEADER:
        return new S2A_INFO_DETAILED_Packet($data);
        	
      case SteamPacket::A2S_INFO_HEADER:
        return new A2S_INFO_Packet();

      case SteamPacket::S2A_INFO2_HEADER:
        return new S2A_INFO2_Packet($data);
        	
      case SteamPacket::A2A_PING_HEADER:
        return new A2A_PING_Packet();

      case SteamPacket::A2A_ACK_HEADER:
        return new A2A_ACK_Packet($data);

      case SteamPacket::A2S_PLAYER_HEADER:
        return new A2A_PLAYER_Packet();
        	
      case SteamPacket::S2A_PLAYER_HEADER:
        return new S2A_PLAYER_Packet($data);

      case SteamPacket::A2S_RULES_HEADER:
        return new A2S_RULES_Packet();
        	
      case SteamPacket::S2A_RULES_HEADER:
        return new S2A_RULES_Packet($data);

      case SteamPacket::A2S_SERVERQUERY_GETCHALLENGE_HEADER:
        return new A2S_SERVERQUERY_GETCHALLENGE_Packet();

      case SteamPacket::S2C_CHALLENGE_HEADER:
        return new S2C_CHALLENGE_Packet($data);

      case SteamPacket::A2M_GET_SERVERS_BATCH2_HEADER:
        return new A2M_GET_SERVERS_BATCH2_Packet($data);

      case SteamPacket::M2A_SERVER_BATCH_HEADER:
        return new M2A_SERVER_BATCH_Packet($data);
        
      case SteamPacket::RCON_GOLDSRC_RESPONSE_HEADER:
      	return new RCONGoldSrcResponse($data);

      default:
        throw new PacketFormatException("Unknown packet with header 0x" . dechex($header) . " received.");
    }
  }
  
  public static function reassemblePacket($splitPackets, $isCompressed = false, $uncompressedSize = 0, $packetChecksum = 0)
  {
    $packetData = "";
     
    foreach($splitPackets as $splitPacket)
    {
      if($splitPacket == null)
      {
        throw new UncompletePacketException();
      }

      $packetData += $splitPacket;
    }

    if($isCompressed)
    {
      $packetData = bzdecompress($packetData);

      if(crc32($packetData) != $packetChecksum)
      {
        throw new PacketFormatException("CRC32 checksum mismatch of uncompressed packet data.");
      }
    }

    return self::getPacketFromData($packetData);
  }
}
?>