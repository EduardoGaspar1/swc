# Configuración de routers – WAN SMART CONTROL

Configuraciones aplicadas a los tres routers de la WAN, SMART CONTROL mediante FRRouting (FRR).

## 1. Router Barcelona

### Configuración de interfaces e IP

```text
configure terminal
interface eth0
ip address 10.0.12.1/30
no shutdown
exit

interface eth1
ip address 10.0.13.1/30
no shutdown
exit

interface eth2
ip address 10.0.1.1/24
no shutdown
exit
```

### Configuración OSPF

```text
router ospf
network 10.0.12.0/30 area 0
network 10.0.13.0/30 area 0
network 10.0.1.0/24 area 0
exit
end
write memory
```

## 2. Router Madrid

### Configuración de interfaces e IP

```text
configure terminal
interface eth0
ip address 10.0.12.2/30
no shutdown
exit

interface eth1
ip address 10.0.2.1/24
no shutdown
exit

interface eth2
ip address 10.0.23.1/30
no shutdown
exit
```

### Configuración OSPF

```text
router ospf
network 10.0.12.0/30 area 0
network 10.0.23.0/30 area 0
network 10.0.2.0/24 area 0
exit
end
write memory
```

## 3. Router Valencia

### Configuración de interfaces e IP

```text
configure terminal
interface eth0
ip address 10.0.3.1/24
no shutdown
exit

interface eth1
ip address 10.0.13.2/30
no shutdown
exit

interface eth2
ip address 10.0.23.2/30
no shutdown
exit
```

### Configuración OSPF

```text
router ospf
network 10.0.13.0/30 area 0
network 10.0.23.0/30 area 0
network 10.0.3.0/24 area 0
exit
end
write memory
```

## 4. Resumen del direccionamiento

| Sede | Interfaz | Dirección IP | Red |
|---|---|---|---|
| Barcelona | eth0 | 10.0.12.1/30 | Barcelona-Madrid |
| Barcelona | eth1 | 10.0.13.1/30 | Barcelona-Valencia |
| Barcelona | eth2 | 10.0.1.1/24 | LAN Barcelona |
| Madrid | eth0 | 10.0.12.2/30 | Barcelona-Madrid |
| Madrid | eth1 | 10.0.2.1/24 | LAN Madrid |
| Madrid | eth2 | 10.0.23.1/30 | Madrid-Valencia |
| Valencia | eth0 | 10.0.3.1/24 | LAN Valencia |
| Valencia | eth1 | 10.0.13.2/30 | Barcelona-Valencia |
| Valencia | eth2 | 10.0.23.2/30 | Madrid-Valencia |

## 5. Protocolo de enrutamiento

Se utiliza OSPF (Open Shortest Path First) en el área 0 para intercambiar rutas entre las tres sedes y permitir la comunicación entre sus redes LAN.

Las configuraciones se guardan mediante el comando `write memory`.
