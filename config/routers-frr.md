
# Configuración de routers FRR
## Proyecto WAN SMART CONTROL

Versión de FRR: 10.6.1

## 1. Router Barcelona

### Interfaces

- eth0: 10.0.12.1/30
- eth1: 10.0.13.1/30
- eth2: 10.0.1.1/24

### Configuración OSPF

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

router ospf
network 10.0.12.0/30 area 0
network 10.0.13.0/30 area 0
network 10.0.1.0/24 area 0
exit
end
write memory
```

---

## 2. Router Madrid

### Interfaces

- eth0: 10.0.12.2/30
- eth1: 10.0.2.1/24
- eth2: 10.0.23.1/30

### Configuración OSPF

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

router ospf
network 10.0.12.0/30 area 0
network 10.0.23.0/30 area 0
network 10.0.2.0/24 area 0
exit
end
write memory
```

---

## 3. Router Valencia

### Interfaces

- eth0: 10.0.3.1/24
- eth1: 10.0.13.2/30
- eth2: 10.0.23.2/30

### Configuración OSPF

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

router ospf
network 10.0.13.0/30 area 0
network 10.0.23.0/30 area 0
network 10.0.3.0/24 area 0
exit
end
write memory
```

---

## Notas

- Protocolo de enrutamiento: OSPF.
- Área utilizada: 0.
- Direccionamiento WAN: subredes /30.
- Direccionamiento LAN: subredes /24.
- Las configuraciones se guardan mediante `write memory`.
